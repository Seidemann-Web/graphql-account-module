<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Review\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class ReviewTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCT_WITH_EXISTING_REVIEW_ID = 'b56597806428de2f58b1c6c7d3e0e093';

    private const PRODUCT_ID = 'b56597806428de2f58b1c6c7d3e0e093';

    private const TEST_PRODUCT_ID = '_test_product_for_rating_5_';

    private const TEXT = 'Best product ever';

    private const TEST_DATA_REVIEW = '94415306f824dc1aa2fce0dc4f12783d';

    private const USERID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const DIFFERENT_USERNAME = 'differentuser@oxid-esales.com';

    private const DIFFERENT_USER_PASSWORD = 'useruser';

    private const REVIEW_TEXT = 'Some text, containing a review for this product.';

    private const PRODUCT_WITH_AVERAGE_RATING = '_test_product_for_rating_avg';

    private $createdReviews = [];

    protected function setUp(): void
    {
        parent::setUp();

        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', true);
    }

    protected function tearDown(): void
    {
        $this->prepareToken(); //admin can delete every review

        foreach ($this->createdReviews as $id) {
            $this->reviewDelete($id);
        }
        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', false);

        parent::tearDown();
    }

    public function testSetReviewWithoutToken(): void
    {
        $result = $this->query(
            'mutation {
                reviewSet(review: {
                    rating: 5,
                    text: "' . self::TEXT . '",
                    productId: "' . self::TEST_PRODUCT_ID . '"
                }){
                    id
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function setReviewDataProvider()
    {
        return [
            'text_only' => [
                'text'   => self::TEXT,
                'rating' => '',
            ],
            'rating_only' => [
                'text'   => '',
                'rating' => 5,

            ],
            'text_and_rating' => [
                'text'   => self::TEXT,
                'rating' => 5,
            ],
        ];
    }

    /**
     * @dataProvider setReviewDataProvider
     */
    public function testSetReview(string $text, string $rating): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->reviewSet(self::TEST_PRODUCT_ID, $text, $rating);
        $this->assertResponseStatus(200, $result);

        $reviewData = $result['body']['data']['reviewSet'];
        $id         = $reviewData['id'];
        $this->assertStringMatchesFormat('%s', $id);
        $this->assertSame(self::TEST_PRODUCT_ID, $reviewData['product']['id']);
        $this->assertEquals($text, $reviewData['text']);
        $this->assertEquals((int) $rating, $reviewData['rating']);

        $result = $this->queryReview($id);
        $this->assertEquals($text, $result['body']['data']['review']['text']);
        $this->assertEquals((int) $rating, $result['body']['data']['review']['rating']);
    }

    public function testSetReviewInvalidInput(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->reviewSet(self::TEST_PRODUCT_ID, null, null);

        $this->assertResponseStatus(400, $result);
        $this->assertSame(
            'Review input cannot have both empty text and rating value.',
            $result['body']['errors'][0]['debugMessage']
        );
    }

    public function testSetReviewRatingOutOfBounds(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->reviewSet(self::TEST_PRODUCT_ID, self::TEXT, '6');

        $this->assertResponseStatus(400, $result);
        $this->assertSame('Rating must be between 1 and 5, was 6', $result['body']['errors'][0]['debugMessage']);
    }

    public function testSetReviewWrongProduct(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->reviewSet('some_not_existing_product', self::TEXT, '5');

        $this->assertResponseStatus(404, $result);
        $this->assertSame(
            'Product was not found by id: some_not_existing_product',
            $result['body']['errors'][0]['message']
        );
    }

    public function testSetMultipleReviewsForOneProduct(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->reviewSet(self::PRODUCT_WITH_EXISTING_REVIEW_ID, self::TEXT, '4');

        $this->assertResponseStatus(404, $result);
        $this->assertSame(
            'Review for product with id: ' . self::PRODUCT_WITH_EXISTING_REVIEW_ID . ' already exists',
            $result['body']['errors'][0]['message']
        );
    }

    /**
     *  NOTE: When querying a customer for reviews, all reviews disregarding of current
     *        langauge are shown. TODO: add Language DataType and relate to customer reviews
     */
    public function testUserReviews(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->queryReviews();

        $this->assertResponseStatus(200, $result);
        $this->assertEquals(3, count($result['body']['data']['customer']['reviews']));
    }

    public function providerProductMultiLanguageReview()
    {
        return [
            'english' => [
                'lang'     => 1,
                'expected' => 1,
            ],
            'german' => [
                'lang'     => 0,
                'expected' => 0,
            ],
        ];
    }

    /**
     * NOTE: When querying a product for reviews, only reviews in the
     *       requested language are shown.
     *
     * @dataProvider providerProductMultiLanguageReview
     */
    public function testProductReviewsByLanguage(string $lang, int $expected): void
    {
        $this->setGETRequestParameter('lang', $lang);
        $result = $this->queryProduct(self::PRODUCT_WITH_EXISTING_REVIEW_ID);

        $this->assertResponseStatus(200, $result);
        $this->assertEquals($expected, count($result['body']['data']['product']['reviews']));
    }

    public function testProductAverageRating(): void
    {
        $this->prepareToken();

        //query, expected result: 2 ratings, average 2.0
        $result = $this->queryProduct(self::PRODUCT_WITH_AVERAGE_RATING);
        $this->assertResponseStatus(200, $result);
        $productRating = $result['body']['data']['product']['rating'];
        $this->assertSame(2, $productRating['count']);
        $this->assertEquals(2.0, $productRating['rating']);

        //create
        $result = $this->reviewSet(self::PRODUCT_WITH_AVERAGE_RATING, self::TEXT, '5');
        $this->assertResponseStatus(200, $result);
        $review = $result['body']['data']['reviewSet'];
        $this->assertSame(5, $review['rating']);

        //query, expected result: 3 ratings, average 3.0
        $result = $this->queryProduct(self::PRODUCT_WITH_AVERAGE_RATING);
        $this->assertResponseStatus(200, $result);
        $productRating = $result['body']['data']['product']['rating'];
        $this->assertEquals(3, $productRating['rating']);
        $this->assertSame(3, $productRating['count']);

        //delete
        $this->reviewDelete($review['id']);

        //query, expected result: 2 ratings, average 2.0
        $result = $this->queryProduct(self::PRODUCT_WITH_AVERAGE_RATING);
        $this->assertResponseStatus(200, $result);
        $productRating = $result['body']['data']['product']['rating'];
        $this->assertEquals(2, $productRating['rating']);
        $this->assertSame(2, $productRating['count']);

        //rate again
        $result = $this->reviewSet(self::PRODUCT_WITH_AVERAGE_RATING, self::TEXT, '4');
        $this->assertResponseStatus(200, $result);
        $rating = $result['body']['data']['reviewSet']['rating'];
        $this->assertSame(4, $rating);

        //query, expected result: 3 ratings, average 2.7
        $result = $this->queryProduct(self::PRODUCT_WITH_AVERAGE_RATING);
        $this->assertResponseStatus(200, $result);
        $productRating = $result['body']['data']['product']['rating'];
        $this->assertSame(2.7, $productRating['rating']);
        $this->assertSame(3, $productRating['count']);
    }

    public function testDeleteReviewWithoutToken(): void
    {
        $result = $this->query('mutation {
            reviewDelete(id: "' . self::TEST_DATA_REVIEW . '")
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testDeleteReviewByOtherUser(): void
    {
        $this->prepareToken(self::DIFFERENT_USERNAME, self::DIFFERENT_USER_PASSWORD);
        $result = $this->reviewSet(self::PRODUCT_ID, self::REVIEW_TEXT, '4');
        $this->assertResponseStatus(200, $result);
        $reviewId = $result['body']['data']['reviewSet']['id'];

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('mutation {
            reviewDelete(id: "' . $reviewId . '")
        }');

        $this->assertResponseStatus(401, $result);
    }

    public function testDeleteNonExistentReview(): void
    {
        $this->prepareToken();

        $result = $this->query('mutation {
            reviewDelete(id: "something-that-does-not-exist")
        }');

        $this->assertResponseStatus(404, $result);
    }

    public function testDeleteFailsIfManageFlagSetToFalse(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);
        $result = $this->reviewSet(self::TEST_PRODUCT_ID, self::REVIEW_TEXT, '4');
        $this->assertResponseStatus(200, $result);
        $reviewId = $result['body']['data']['reviewSet']['id'];

        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', false);

        $result = $this->query('mutation {
            reviewDelete(id: "' . $reviewId . '")
        }');

        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', true);

        $this->assertResponseStatus(
            401,
            $result
        );
    }

    private function reviewSet(string $productId, ?string $text, ?string $rating): array
    {
        $query = 'mutation {
                    reviewSet(review: {
                        productId: "' . $productId . '",
                 ';

        if (!empty($text)) {
            $query .= ' text: "' . $text . '"
                       ';
        }

        if (!empty($rating)) {
            $query .= " rating: {$rating} ";
        }
        $query .= ' }
                      ){
                            id
                            product{
                                id
                            }
                            text
                            rating
                        }
                    }';

        $result = $this->query($query);

        if (isset($result['body']['data']['reviewSet']['id'])) {
            $this->createdReviews[$result['body']['data']['reviewSet']['id']] = $result['body']['data']['reviewSet']['id'];
        }

        return $result;
    }

    private function reviewDelete(string $id): void
    {
        if (isset($this->createdReviews[$id])) {
            unset($this->createdReviews[$id]);
        }

        $result = $this->query(
            'mutation {
                     reviewDelete(id: "' . $id . '")
                }'
        );
        $this->assertResponseStatus(200, $result);
        $this->assertEquals(true, $result['body']['data']['reviewDelete']);
    }

    private function queryReview(string $id): array
    {
        return $this->query(
            'query {
                review(id: "' . $id . '") {
                    id
                    text
                    rating
                }
            }'
        );
    }

    private function queryProduct(string $productId): array
    {
        return $this->query(
            'query {
                product(id: "' . $productId . '") {
                    rating {
                        rating
                        count
                    }
                    reviews {
                        active
                        id
                        text
                        rating
                    }
                }
            }'
        );
    }

    private function queryReviews(): array
    {
        return $this->query(
            'query{
                customer {
                    reviews{
                        id
                        text
                        rating
                    }
                }
            }'
        );
    }
}
