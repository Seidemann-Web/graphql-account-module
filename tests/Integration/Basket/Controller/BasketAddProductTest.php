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

    public function testAddProductToBasketNoToken(): void
    {
        $result = $this->query('
            mutation {
                basketAddProduct(
                    basketId: "' . self::PUBLIC_BASKET . '"
                    productId: "' . self::PRODUCT_ID . '"
                    amount: 1
                ) {
                    id
                }
            }
        ');

        $this->assertResponseStatus(400, $result);
    }

    public function testAddProductToBasketWrongBasketId(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('
            mutation {
                basketAddProduct(
                    basketId: "non_existing_basket_id"
                    productId: "' . self::PRODUCT_ID . '"
                    amount: 1
                ) {
                    id
                }
            }
        ');

        $this->assertResponseStatus(404, $result);
    }

    public function testAddProductToBasket(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('
            mutation {
                basketAddProduct(
                    basketId: "' . self::PUBLIC_BASKET . '"
                    productId: "' . self::PRODUCT_ID . '"
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
            }
        ');

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
}
