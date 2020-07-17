<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasket;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class BasketRemoveMultishopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const OTHER_PASSWORD = 'useruser';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PUBLIC_BASKET = '_test_basket_public'; //owned by shop1 user

    private const PRIVATE_BASKET = '_test_basket_private'; //owned by otheruser

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

    public function testRemoveBasketFromDifferentShopWithTokenForMallUser(): void
    {
        $this->markTestIncomplete('TODO: finish during roundtrip testing create/remove'); //TODO

        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->removeBasket(self::PRIVATE_BASKET);
        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketRemove']);
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

    private function removeBasket(string $id): array
    {
        return $this->query(
            'mutation{
                basketRemove(id: "' . $id . '")
            }'
        );
    }

    private function getBasket(): EshopUserBasket
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);

        return $user->getBasket(self::PRIVATE_BASKET);
    }
}
