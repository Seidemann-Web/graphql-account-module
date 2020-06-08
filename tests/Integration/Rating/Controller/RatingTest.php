<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishedPrice\Controller;

use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class RatingTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';
    private const PASSWORD = 'useruser';
    private const USERID = 'e7af1c3b786fd02906ccd75698f4e6b9';
    private const PRODUCTID = '058c7b525aad619d8b343c0ffada0247';

    public function testSetRatingWithoutToken()
    {
        $result = $this->query(
            'mutation {
                ratingSet(ratingInput: {
                    rating: 5,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testSetRating()
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingSet(ratingInput: {
                    rating: 5,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                    product{
                        id
                    }
                    rating
                    user{
                        id
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $ratingData = $result['body']['data']['ratingSet'];

        $id = $ratingData['id'];
        $this->assertStringMatchesFormat("%s", $id);
        $this->assertSame(self::PRODUCTID, $ratingData['product']['id']);
        $this->assertSame(5, $ratingData['rating']);
        $this->assertSame(self::USERID, $ratingData['user']['id']);

        $result = $this->query(
            'query {
                rating(ratingInput: {
                    rating: 5,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                    product{
                        id
                    }
                    rating
                    user{
                        id
                    }
                }
            }'
        );
    }

    public function testSetRatingOutOfBounds()
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingSet(ratingInput: {
                    rating: 6,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                    product{
                        id
                    }
                    rating
                    user{
                        id
                    }
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
        $this->assertSame("Rating should be in 1 to 5 interval", $result['body']['errors'][0]['debugMessage']);
    }

    public function testSetRatingWrongProduct()
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingSet(ratingInput: {
                    rating: 5,
                    productId: "some_not_existing_product"
                }){
                    id
                    product{
                        id
                    }
                    rating
                    user{
                        id
                    }
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
        $this->assertSame(
            "Product was not found by id: some_not_existing_product",
            $result['body']['errors'][0]['message']
        );
    }
}
