<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketRemoveTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    public function testRemoveBasketNoToken(): void
    {
        $basketId = $this->createBasket();

        $result = $this->basketRemoveMutation($basketId);

        $this->assertResponseStatus(400, $result);

        $this->deleteBasket($basketId);
    }

    public function testRemoveBasketNotFound(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->basketRemoveMutation('this_is_no_saved_basket_id');

        $this->assertResponseStatus(404, $result);
    }

    public function testRemoveBasketOfOtherCustomer(): void
    {
        $basketId = $this->createBasket();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $result = $this->basketRemoveMutation($basketId);

        $this->assertResponseStatus(401, $result);

        $this->deleteBasket($basketId);
    }

    public function testRemoveBasketWithToken(): void
    {
        $basketId = $this->createBasket();

        $this->prepareToken(self::USERNAME, self::PASSWORD);
        $result = $this->basketRemoveMutation($basketId);

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketRemove']);

        $this->deleteBasket($basketId);
    }

    public function testRemoveBasketWithAdminToken(): void
    {
        $basketId = $this->createBasket();

        $this->prepareToken();
        $result = $this->basketRemoveMutation($basketId);

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketRemove']);

        $this->deleteBasket($basketId);
    }

    private function createBasket(): string
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            basketCreate(basket: {title: "new-basket-list"}) {
                id
            }
        }');

        $this->setAuthToken('');

        return $result['body']['data']['basketCreate']['id'];
    }

    private function deleteBasket(string $basketId): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);
        $this->basketRemoveMutation($basketId);
    }

    private function basketRemoveMutation(string $basketId): array
    {
        return $this->query(
            'mutation {
                basketRemove(id: "' . $basketId . '")
            }'
        );
    }
}
