<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Rating\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class RatingMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const RATING_SHOP_1 = 'test_rating_1_';

    private const RATING_SHOP_2 = 'test_rating_8_';

    private const RATING_TO_BE_DELETED = 'test_rating_delete_';

    public function dataProviderRatingPerShop()
    {
        return [
            ['1', self::RATING_SHOP_1],
            ['2', self::RATING_SHOP_2],
        ];
    }

    /**
     * @dataProvider dataProviderRatingPerShop
     */
    public function testUserRatingPerShop(string $shopId, string $ratingId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                rating(id: "' . $ratingId . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
    }

    /**
     * @dataProvider dataProviderRatingPerShop
     */
    public function testAdminRatingPerShop(string $shopId, string $ratingId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken();

        $result = $this->query(
            'query{
                rating(id: "' . $ratingId . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
    }

    public function testGetUserRatingFromShop1ToShop2(): void
    {
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                rating(id: "' . self::RATING_SHOP_1 . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testDeleteShop1RatingFromShop2(): void
    {
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingDelete(id: "' . self::RATING_TO_BE_DELETED . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }
}
