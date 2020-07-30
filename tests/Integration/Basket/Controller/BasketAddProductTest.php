<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketAddProductTest extends TokenTestCase
{
    // Public basket
    private const PUBLIC_BASKET = '_test_basket_public';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCT = '_test_product_for_basket';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const PRIVATE_BASKET = '_test_basket_private';

    private const PRODUCT_FOR_PRIVATE_BASKET = '_test_product_for_wish_list';

    public function testAddProductToBasketNoToken(): void
    {
        $result = $this->basketAddProductMutation(self::PUBLIC_BASKET, self::PRODUCT_ID);

        $this->assertResponseStatus(400, $result);
    }

    public function testAddProductToBasketWrongBasketId(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->basketAddProductMutation('non_existing_basket_id', self::PRODUCT_ID);

        $this->assertResponseStatus(404, $result);
    }

    public function testAddProductToBasket(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->basketAddProductMutation(self::PUBLIC_BASKET, self::PRODUCT_ID, 2);

        $this->assertResponseStatus(200, $result);

        $this->assertSame(
            [
                'id'    => self::PUBLIC_BASKET,
                'items' => [
                    [
                        'product' => [
                            'id' => self::PRODUCT_ID,
                        ],
                        'amount' => 2,
                    ], [
                        'product' => [
                            'id' => self::PRODUCT,
                        ],
                        'amount' => 1,
                    ],
                ],
            ],
            $result['body']['data']['basketAddProduct']
        );
    }

    public function testAddNonExistingProductToBasket(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->basketAddProductMutation(self::PUBLIC_BASKET, 'non_existing_product');

        $this->assertResponseStatus(404, $result);
    }

    public function testAddProductToSomeoneElseBasket(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);
        $result = $this->basketAddProductMutation(self::PRIVATE_BASKET, self::PRODUCT_FOR_PRIVATE_BASKET);

        $this->assertResponseStatus(401, $result);
    }

    private function basketAddProductMutation(string $basketId, string $productId, int $amount = 1): array
    {
        return $this->query('
            mutation {
                basketAddProduct(
                    basketId: "' . $basketId . '",
                    productId: "' . $productId . '",
                    amount: ' . $amount . '
                ) {
                    id
                    items {
                        product {
                            id
                        }
                        amount
                    }
                }
            }
        ');
    }
}
