<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketAddProductMultishopTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const PUBLIC_BASKET = '_test_basket_public'; //owned by shop1 user

    private const PRIVATE_BASKET = '_test_basket_private'; //owned by otheruser

    private const SHOP_1_PRODUCT_ID = '_test_product_wished_price_3_';

    private const SHOP_2_PRODUCT_ID = '_test_product_5_';

    public function dataProviderAddProductToBasketPerShop()
    {
        return [
            'shop_1' => [
                'shopId'    => '1',
                'basketId'  => self::PUBLIC_BASKET,
                'productId' => self::SHOP_1_PRODUCT_ID,
            ],
            'shop_2' => [
                'shopId'    => '2',
                'basketId'  => '_test_shop2_basket_public',
                'productId' => self::SHOP_2_PRODUCT_ID,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderAddProductToBasketPerShop
     */
    public function testAddProductToBasketPerShop(string $shopId, string $basketId, string $productId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);
        $this->assignUserToShop((int) $shopId);

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                 basketAddProduct(
                    basketId: "' . $basketId . '"
                    productId: "' . $productId . '"
                    amount: 2
                 ) {
                    id
                    items {
                        product {
                            id
                        }
                        amount
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertSame(
            [
                'id'    => $basketId,
                'items' => [
                    [
                        'product' => [
                            'id' => $productId,
                        ],
                        'amount' => 2,
                    ], [
                        'product' => [
                            'id' => '_test_product_for_basket',
                        ],
                        'amount' => 1,
                    ],
                ],
            ],
            $result['body']['data']['basketAddProduct']
        );
    }

    public function testAddProductToBasketFromOtherSubshop(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');
        $this->assignUserToShop(1);

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query(
            'mutation {
                 basketAddProduct(
                    basketId: "' . self:: PRIVATE_BASKET . '"
                    productId: "' . self::SHOP_2_PRODUCT_ID . '"
                    amount: 2
                 ) {
                    id
                    items {
                        product {
                            id
                        }
                        amount
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertSame(
            [
                'id'    => self:: PRIVATE_BASKET,
                'items' => [
                    [
                        'product' => [
                            'id' => self::SHOP_2_PRODUCT_ID,
                        ],
                        'amount' => 2,
                    ], [
                        'product' => [
                            'id' => '_test_product_for_basket',
                        ],
                        'amount' => 1,
                    ],
                ],
            ],
            $result['body']['data']['basketAddProduct']
        );
    }

    private function assignUserToShop(int $shopId): void
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);
        $user->assign(
            [
                'oxshopid' => $shopId,
            ]
        );
        $user->save();
    }
}
