<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketRemoveProductTest extends TokenTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const BASKET_ID = '_test_basket_private';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const PRODUCT_ID_1 = '_test_product_for_rating_avg';

    private const PRODUCT_ID_2 = '_test_product_for_basket';

    public function testRemoveBasketProductWithoutToken(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->basketAddProductMutation(self::BASKET_ID, self::PRODUCT_ID);

        $this->setAuthToken('');
        $result = $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID);
        $this->assertResponseStatus(400, $result);
    }

    public function testRemoveBasketProductUsingDifferentUser(): void
    {
        $this->prepareToken();

        $result = $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID_2);
        $this->assertResponseStatus(401, $result);
    }

    public function testRemoveBasketProduct(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->basketAddProductMutation(self::BASKET_ID, self::PRODUCT_ID);

        $result = $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID);
        $this->assertResponseStatus(200, $result);

        $items = $result['body']['data']['basketRemoveProduct']['items'];
        $this->assertCount(1, $items);

        foreach ($items as $item) {
            $this->assertTrue(self::PRODUCT_ID !== $item['product']['id']);
        }
    }

    public function testDecreaseBasketProductAmount(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $result = $this->basketAddProductMutation(self::BASKET_ID, self::PRODUCT_ID, 3);
        $items  = $result['body']['data']['basketAddProduct']['items'];
        $this->assertCount(2, $items);

        foreach ($items as $item) {
            if (self::PRODUCT_ID === $item['product']['id']) {
                $this->assertSame(3, $item['amount']);
            }
        }

        $result = $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID, 2);
        $this->assertResponseStatus(200, $result);

        $items = $result['body']['data']['basketRemoveProduct']['items'];
        $this->assertCount(2, $items);

        foreach ($items as $item) {
            if (self::PRODUCT_ID === $item['product']['id']) {
                $this->assertSame(1, $item['amount']);
            }
        }

        // clean up database
        $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID);
    }

    public function testRemoveWrongProductFromBasket(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->basketAddProductMutation(self::BASKET_ID, self::PRODUCT_ID_1);

        $result = $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID);
        $this->assertResponseStatus(404, $result);

        // clean up database
        $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID_1);
    }

    public function testRemoveNonExistingProductFromBasket(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->basketRemoveProductMutation(self::BASKET_ID, 'not_a_product');
        $this->assertResponseStatus(404, $result);
    }

    public function testRemoveAllProductsFromBasket(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->basketRemoveProductMutation(self::BASKET_ID, self::PRODUCT_ID_2);
        $this->assertResponseStatus(200, $result);

        $items = $result['body']['data']['basketRemoveProduct']['items'];
        $this->assertCount(0, $items);
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

    private function basketRemoveProductMutation(string $basketId, string $productId, int $amount = 0): array
    {
        return $this->query(
            'mutation {
                basketRemoveProduct(
                    basketId: "' . $basketId . '",
                    productId: "' . $productId . '",
                    amount: ' . $amount . '
                ) {
                    items(pagination: {limit: 10, offset: 0}) {
                        product {
                            id
                            title
                        }
                        amount
                    }
                    id
                }
            }'
        );
    }
}
