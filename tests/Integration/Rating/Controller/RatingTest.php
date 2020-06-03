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

        $this->assertStringMatchesFormat("%s", $ratingData['id']);
        $this->assertSame(self::PRODUCTID, $ratingData['product']['id']);
        $this->assertSame(5, $ratingData['rating']);
        $this->assertSame(self::USERID, $ratingData['user']['id']);
    }
}
