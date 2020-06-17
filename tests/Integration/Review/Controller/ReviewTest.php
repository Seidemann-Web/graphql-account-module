<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Review\Controller;

use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class ReviewTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCTID = '058c7b525aad619d8b343c0ffada0247';

    private const TEXT = 'Best product ever';

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxreviews', 'oxid');

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
}
