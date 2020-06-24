<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Review\Controller;

use OxidEsales\Eshop\Application\Model\Review as EshopReviewModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class ReviewTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCTID = '058c7b525aad619d8b343c0ffada0247';

    private const TEXT = 'Best product ever';

    private const REVIEW_TO_DELETE = 'review_to_delete';

    private const USERID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const PRODUCT_ID = 'b56597806428de2f58b1c6c7d3e0e093';

    private const REVIEW_TEXT = 'Some text, containing a review for this product.';

    protected function setUp(): void
    {
        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', true);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTable('oxreviews', 'oxid');
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
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testSetReview(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                reviewSet(review: {
                    productId: "' . self::PRODUCTID . '",
                    text: "' . self::TEXT . '",
                    rating: 5
                }){
                    id
                    product{
                        id
                    }
                    text
                    rating
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $reviewData = $result['body']['data']['reviewSet'];

        $id = $reviewData['id'];
        $this->assertStringMatchesFormat('%s', $id);
        $this->assertSame(self::PRODUCTID, $reviewData['product']['id']);
        $this->assertSame(self::TEXT, $reviewData['text']);
        $this->assertSame(5, $reviewData['rating']);

        $result = $this->query(
            'query {
                review(id: "' . $id . '") {
                    id
                    text
                    rating
                }
            }'
        );

        $this->assertSame(self::TEXT, $result['body']['data']['review']['text']);
        $this->assertSame(5, $result['body']['data']['review']['rating']);
    }

    public function testSetReviewRatingOutOfBounds(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                reviewSet(review: {
                    productId: "' . self::PRODUCTID . '",
                    text: "' . self::TEXT . '",
                    rating: 6,
                }){
                    id
                    product{
                        id
                    }
                    text
                    rating
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
        $this->assertSame('Rating must be between 1 and 5, was 6', $result['body']['errors'][0]['debugMessage']);
    }

    public function testSetReviewWrongProduct(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                reviewSet(review: {
                    productId: "some_not_existing_product",
                    text: "' . self::TEXT . '",
                    rating: 5
                }){
                    id
                    product{
                        id
                    }
                    text
                    rating
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
        $this->assertSame(
            'Product was not found by id: some_not_existing_product',
            $result['body']['errors'][0]['message']
        );
    }

    public function deleteReviewDataProvider()
    {
        return [
            [
                'withUserToken' => true,
                'expected'      => 200,
            ],
            [
                'withUserToken' => false,
                'expected'      => 400,
            ],
        ];
    }

    /**
     * @dataProvider deleteReviewDataProvider
     */
    public function testDeleteActiveReview(bool $withUserToken, int $expected): void
    {
        $review = oxNew(EshopReviewModel::class);
        $review->assign([
            'oxid'       => self::REVIEW_TO_DELETE,
            'oxshopid'   => '1',
            'oxuserid'   => self::USERID,
            'oxtype'     => 'oxarticle',
            'oxobjectid' => self::PRODUCT_ID,
            'oxtext'     => self::REVIEW_TEXT,
            'oxlang'     => '1',
            'oxrating'   => 4,
        ]);

        $review->save();

        if ($withUserToken) {
            $this->prepareToken(self::USERNAME, self::PASSWORD);
        }

        $result = $this->query('mutation {
            reviewDelete(id: "' . self::REVIEW_TO_DELETE . '")
        }');

        $this->assertResponseStatus($expected, $result);

        if ($expected === 200) {
            $this->assertEquals(true, $result['body']['data']['reviewDelete']);
        }
    }

    /**
     * @dataProvider deleteReviewDataProvider
     */
    public function testDeleteInactiveReview(bool $withUserToken, int $expected): void
    {
        $review = oxNew(EshopReviewModel::class);
        $review->assign([
            'oxid'       => self::REVIEW_TO_DELETE,
            'oxshopid'   => '1',
            'oxuserid'   => self::USERID,
            'oxtype'     => 'oxarticle',
            'oxobjectid' => self::PRODUCT_ID,
            'oxtext'     => self::REVIEW_TEXT,
            'oxlang'     => '1',
            'oxrating'   => 4,
            'oxactive'   => false,
        ]);

        $review->save();

        if ($withUserToken) {
            $this->prepareToken(self::USERNAME, self::PASSWORD);
        }

        $result = $this->query('mutation {
            reviewDelete(id: "' . self::REVIEW_TO_DELETE . '")
        }');

        $this->assertResponseStatus($expected, $result);

        if ($expected === 200) {
            $this->assertEquals(true, $result['body']['data']['reviewDelete']);
        }
    }

    public function testDeleteReviewByOtherUser(): void
    {
        $review = oxNew(EshopReviewModel::class);
        $review->assign([
            'oxid'       => self::REVIEW_TO_DELETE,
            'oxshopid'   => '1',
            'oxuserid'   => self::USERID,
            'oxtype'     => 'oxarticle',
            'oxobjectid' => self::PRODUCT_ID,
            'oxtext'     => self::REVIEW_TEXT,
            'oxlang'     => '1',
            'oxrating'   => 4,
            'oxactive'   => false,
        ]);

        $review->save();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('mutation {
            reviewDelete(id: "' . self::REVIEW_TO_DELETE . '")
        }');

        $this->assertResponseStatus(401, $result);
    }

    public function testDeleteReviewByAdmin(): void
    {
        $review = oxNew(EshopReviewModel::class);
        $review->assign([
            'oxid'       => self::REVIEW_TO_DELETE,
            'oxshopid'   => '1',
            'oxuserid'   => self::USERID,
            'oxtype'     => 'oxarticle',
            'oxobjectid' => self::PRODUCT_ID,
            'oxtext'     => self::REVIEW_TEXT,
            'oxlang'     => '1',
            'oxrating'   => 4,
            'oxactive'   => false,
        ]);

        $review->save();

        $this->prepareToken();

        $result = $this->query('mutation {
            reviewDelete(id: "' . self::REVIEW_TO_DELETE . '")
        }');

        $this->assertResponseStatus(200, $result);

        $this->assertEquals(true, $result['body']['data']['reviewDelete']);
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
        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', false);
        $review = oxNew(EshopReviewModel::class);
        $review->assign([
            'oxid'       => self::REVIEW_TO_DELETE,
            'oxshopid'   => '1',
            'oxuserid'   => self::USERID,
            'oxtype'     => 'oxarticle',
            'oxobjectid' => self::PRODUCT_ID,
            'oxtext'     => self::REVIEW_TEXT,
            'oxlang'     => '1',
            'oxrating'   => 4,
        ]);

        $review->save();

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            reviewDelete(id: "' . self::REVIEW_TO_DELETE . '")
        }');

        Registry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', true);

        $this->assertResponseStatus(
            401,
            $result
        );
    }
}
