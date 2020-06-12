<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishedPrice\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class WishedPriceMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const WISHED_PRICE_SHOP_1 = '_test_wished_price_1_';

    private const WISHED_PRICE_SHOP_2 = '_test_wished_price_8_';

    private const WISHED_PRICE_TO_BE_DELETED = '_test_wished_price_delete_';

    public function dataProviderWishedPricePerShop()
    {
        return [
            ['1', self::WISHED_PRICE_SHOP_1],
            ['2', self::WISHED_PRICE_SHOP_2],
        ];
    }

    /**
     * @dataProvider dataProviderWishedPricePerShop
     */
    public function testUserWishedPricePerShop(string $shopId, string $wishedPriceId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                wishedPrice(id: "' . $wishedPriceId . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
    }

    /**
     * @dataProvider dataProviderWishedPricePerShop
     */
    public function testAdminWishedPricePerShop(string $shopId, string $wishedPriceId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken();

        $result = $this->query(
            'query{
                wishedPrice(id: "' . $wishedPriceId . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
    }

    public function testGetUserWishedPriceFromShop1ToShop2(): void
    {
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                wishedPrice(id: "' . self::WISHED_PRICE_SHOP_1 . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testDeleteShop1WishedPriceFromShop2(): void
    {
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                wishedPriceDelete(id: "' . self::WISHED_PRICE_TO_BE_DELETED . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }
}
