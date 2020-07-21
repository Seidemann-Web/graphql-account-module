<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class BasketRemoveMultishopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PUBLIC_BASKET = '_test_basket_public'; //owned by shop1 user

    private const PRIVATE_BASKET = '_test_basket_private'; //owned by otheruser

    // TODO: Check whether this constant exists in basket classes and use it instead
    private const BASKET_NOTICE_LIST = 'noticelist';

    public function testRemoveNotOwnedBasketFromDifferentShop(): void
    {
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->removeBasket(self::PRIVATE_BASKET);
        $this->assertResponseStatus(401, $result);
    }

    public function testRemoveBasketFromDifferentShopNoToken(): void
    {
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $result = $this->removeBasket(self::PUBLIC_BASKET);
        $this->assertResponseStatus(400, $result);
    }

    public function testRemoveOwnedBasketFromDifferentShop(): void
    {
        EshopRegistry::getConfig()->setShopId('1');
        $this->setGETRequestParameter('shp', '1');
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->createBasket(self::BASKET_NOTICE_LIST, 'false');
        $this->assertResponseStatus(200, $result);
        $basketId = $result['body']['data']['basketCreate']['id'];

        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->removeBasket($basketId);
        $this->assertResponseStatus(401, $result);

        EshopRegistry::getConfig()->setShopId('1');
        $this->setGETRequestParameter('shp', '1');
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->removeBasket($basketId);
        $this->assertResponseStatus(200, $result);
    }

    public function testRemoveBasketFromDifferentShopWithTokenForMallUser(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->createBasket(self::BASKET_NOTICE_LIST, 'false');
        $this->assertResponseStatus(200, $result);
        $basketId = $result['body']['data']['basketCreate']['id'];

        $result = $this->removeBasket($basketId);
        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketRemove']);

        $result = $this->queryBasket($basketId);
        $this->assertResponseStatus(404, $result);
    }

    private function removeBasket(string $id): array
    {
        return $this->query(
            'mutation{
                basketRemove(id: "' . $id . '")
            }'
        );
    }

    private function createBasket(string $title, string $public = 'true'): array
    {
        return $this->query(
            'mutation {
                basketCreate(basket: {title: "' . $title . '", public: ' . $public . '}) {
                    id
                }
            }'
        );
    }

    private function queryBasket(string $id): array
    {
        return $this->query(
            'query {
                basket(id: "' . $id . '"){
                    id
                }
            }'
        );
    }
}
