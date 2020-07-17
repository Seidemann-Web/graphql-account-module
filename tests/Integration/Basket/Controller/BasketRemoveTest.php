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
    private const PUBLIC_BASKET = '_test_basket_public';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    public function testRemoveBasketNoToken(): void
    {
        $result = $this->query(
            'mutation{
                basketRemove(id: "' . self::PUBLIC_BASKET . '")
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testRemoveBasketNotFound(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation{
                basketRemove(id: "this_is_no_saved_basket_id")
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testRemoveBasketOfOtherCustomer(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query(
            'mutation{
                basketRemove(id: "' . self::PUBLIC_BASKET . '")
            }'
        );

        $this->assertResponseStatus(401, $result);
    }

    public function testRemoveBasketWithToken(): void
    {
        $this->markTestIncomplete('TODO: finish during roundtrip testing create/remove'); //TODO

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation{
                basketRemove(id: "' . self::PUBLIC_BASKET . '")
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketRemove']);
    }

    public function testRemoveBasketWithAdminToken(): void
    {
        $this->markTestIncomplete('TODO: finish during roundtrip testing create/remove'); //TODO

        $this->prepareToken();

        $result = $this->query(
            'mutation{
                basketRemove(id: "' . self::PUBLIC_BASKET . '")
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketRemove']);
    }
}
