<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishList\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasket;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Account\WishList\Service\WishList as WishListService;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class WishListMultiShopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const OTHER_PASSWORD = 'useruser';

    private const SHOP_1_PRODUCT_ID = '_test_product_wished_price_3_';

    private const SHOP_2_PRODUCT_ID = '_test_product_5_';

    public function dataProviderWishListPerShop()
    {
        return [
            'shop_1' => [
                'shopid'    => '1',
                'productId' => self::SHOP_1_PRODUCT_ID,
            ],
            'shop_2' => [
                'shopid'    => '2',
                'productId' => self::SHOP_2_PRODUCT_ID,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderWishListPerShop
     */
    public function testAddProductToWishListPerShop(string $shopId, string $productId): void
    {
        $this->assignUserToShop((int) $shopId);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);
        $this->getWishList()->delete();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->addProductToWishListMutation($productId);

        $this->assertResponseStatus(200, $result);

        $products = $this->getWishListArticles();
        $this->assertSame($productId, array_pop($products)->getId());
    }

    public function testAddProductToWishListForMallUserFromOtherSubshop(): void
    {
        $this->assignUserToShop(1);

        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');
        $this->getWishList()->delete();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->addProductToWishListMutation(self::SHOP_2_PRODUCT_ID);

        $this->assertResponseStatus(200, $result);

        $products = $this->getWishListArticles();
        $this->assertSame(self::SHOP_2_PRODUCT_ID, array_pop($products)->getId());
    }

    /**
     * @dataProvider dataProviderWishListPerShop
     */
    public function testRemoveProductFromWishListPerShop(string $shopId, string $productId): void
    {
        $this->assignUserToShop((int) $shopId);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);
        $this->getWishList()->delete();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $this->addProductToWishListMutation($productId);

        $result = $this->removeProductFromWishListMutation($productId);
        $this->assertResponseStatus(200, $result);

        $products = $this->getWishListArticles();
        $this->assertEmpty($products);
    }

    /**
     * @dataProvider dataProviderWishListPerShop
     */
    public function testRemoveProductFromWishListFromOtherSubshop(string $shopId, string $productId): void
    {
        $this->assignUserToShop((int) $shopId);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);
        $this->getWishList()->delete();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $this->addProductToWishListMutation($productId);

        $removeProductId = $shopId == 1 ? self::SHOP_2_PRODUCT_ID : self::SHOP_1_PRODUCT_ID;
        $result          = $this->removeProductFromWishListMutation($removeProductId);
        $this->assertResponseStatus(404, $result);

        $products = $this->getWishListArticles();
        $this->assertCount(1, $products);
        $this->assertSame($productId, array_pop($products)->getId());
    }

    private function addProductToWishListMutation(string $productId): array
    {
        return $this->query(
            'mutation{
                 wishListAddProduct(productId: "' . $productId . '"){
                id
              }
            }'
        );
    }

    private function removeProductFromWishListMutation(string $productId): array
    {
        return $this->query(
            'mutation {
                wishListRemoveProduct(productId: "' . $productId . '") {
                    id
                    public
                    creationDate
                    lastUpdateDate
                }
            }'
        );
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

    private function getWishList(): EshopUserBasket
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);

        return $user->getBasket(WishListService::SHOP_WISH_LIST_NAME);
    }

    private function getWishListArticles(): array
    {
        return $this->getWishList()->getArticles();
    }
}
