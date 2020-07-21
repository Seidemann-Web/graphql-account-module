<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketTest extends TokenTestCase
{
    // Public basket
    private const PUBLIC_BASKET = '_test_basket_public';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    // Private basket
    private const PRIVATE_BASKET = '_test_basket_private';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const PRODUCT = '_test_product_for_basket';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    public function testGetPublicBasket(): void
    {
        $result = $this->query(
            'query{
                basket(id: "' . self::PUBLIC_BASKET . '") {
                    items {
                        id
                        amount
                        lastUpdateDate
                        product {
                            id
                        }
                    }
                    id
                    public
                    creationDate
                    lastUpdateDate
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $basket = $result['body']['data']['basket'];
        $this->assertEquals(self::PUBLIC_BASKET, $basket['id']);
        $this->assertEquals(true, $basket['public']);
        $this->assertNull($basket['lastUpdateDate']);

        $this->assertCount(1, $basket['items']);
        $basketItem = $basket['items'][0];
        $this->assertEquals('_test_basket_item_1', $basketItem['id']);
        $this->assertEquals(1, $basketItem['amount']);
        $this->assertEquals(self::PRODUCT, $basketItem['product']['id']);
    }

    public function testGetPrivateBasketAuthorized(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query(
            'query{
                basket(id: "' . self::PRIVATE_BASKET . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $basket = $result['body']['data']['basket'];

        $this->assertEquals(self::PRIVATE_BASKET, $basket['id']);
    }

    public function boolDataProvider(): array
    {
        return [
            [
                true,
            ],
            [
                false,
            ],
        ];
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testGetPrivateBasketNotAuthorized(bool $isLogged): void
    {
        if ($isLogged) {
            $this->prepareToken(self::USERNAME, self::PASSWORD);
        }

        $result = $this->query(
            'query{
                basket(id: "' . self::PRIVATE_BASKET . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(403, $result);
    }

    public function testAddProductToBasketNoToken(): void
    {
        $result = $this->query('
            mutation{
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
            mutation{
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
            mutation{
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
