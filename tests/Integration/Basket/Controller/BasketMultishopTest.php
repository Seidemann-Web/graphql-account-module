<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class BasketMultishopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const OTHER_PASSWORD = 'useruser';

    private const SHOP_1_PRODUCT_ID = '_test_product_wished_price_3_';

    private const SHOP_2_PRODUCT_ID = '_test_product_5_';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PUBLIC_BASKET = '_test_basket_public'; //owned by shop1 user

    private const PRIVATE_BASKET = '_test_basket_private'; //owned by otheruser

    // TODO: Check whether this constant exists in basket classes and use it instead
    private const BASKET_NOTICE_LIST = 'noticelist';

    public function testGetNotOwnedBasketFromDifferentShop(): void
    {
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->queryBasket(self::PRIVATE_BASKET);
        $this->assertResponseStatus(403, $result);
    }

    public function testGetPublicBasketFromDifferentShopNoToken(): void
    {
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $result = $this->queryBasket(self::PUBLIC_BASKET);
        $this->assertResponseStatus(200, $result);
    }

    public function testGetPrivateBasketFromDifferentShopWithTokenForMallUser(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->queryBasket(self::PRIVATE_BASKET);
        $this->assertResponseStatus(200, $result);
    }

    public function testGetPrivateBasketFromSubShopWithToken(): void
    {
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');
        $this->assignUserToShop(2);

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->queryBasket(self::PRIVATE_BASKET);
        $this->assertResponseStatus(200, $result);
    }

    public function testCreatePrivateBasketFromDifferentShop(): void
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

        $result = $this->queryBasket($basketId);
        $this->assertResponseStatus(403, $result);

        EshopRegistry::getConfig()->setShopId('1');
        $this->setGETRequestParameter('shp', '1');
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->removeBasket($basketId);
        $this->assertResponseStatus(200, $result);
    }

    public function testCreatePrivateBasketFromDifferentShopForMallUser(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId('1');
        $this->setGETRequestParameter('shp', '1');
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->createBasket(self::BASKET_NOTICE_LIST, 'false');
        $this->assertResponseStatus(200, $result);
        $basketId = $result['body']['data']['basketCreate']['id'];

        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->queryBasket($basketId);
        $this->assertResponseStatus(200, $result);

        $result = $this->removeBasket($basketId);
        $this->assertResponseStatus(200, $result);
    }

    private function assignUserToShop(int $shopid): void
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);
        $user->assign(
            [
                'oxshopid' => $shopid,
            ]
        );
        $user->save();
    }

    private function queryBasket(string $id): array
    {
        return $this->query('query {
            basket(id: "' . $id . '") {
                id
                public
            }
        }');
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

    private function removeBasket(string $id): array
    {
        return $this->query(
            'mutation {
                basketRemove(id: "' . $id . '")
            }'
        );
    }
}
