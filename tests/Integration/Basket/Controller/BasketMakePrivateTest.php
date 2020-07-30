<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketMakePrivateTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'admin';

    private const OTHER_PASSWORD = 'admin';

    private $basketId;

    public function setUp(): void
    {
        parent::setUp();

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                basketCreate(basket: {
                    title: "test_basket",
                    public: true
                }) {
                    id
                }
            }'
        );

        $this->basketId = $result['body']['data']['basketCreate']['id'];
    }

    public function tearDown(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $this->query(
            'mutation {
                basketRemove(id: "' . $this->basketId . '")
            }'
        );

        parent::tearDown();
    }

    public function testMakePrivateBasketNotFound(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                basketMakePrivate(id: "this_is_no_saved_basket_id"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testMakePrivateBasketOfOtherCustomer(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query(
            'mutation {
                basketMakePrivate(id: "' . $this->basketId . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(403, $result);
    }

    public function testMakePrivateBasketWithToken(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                basketMakePrivate(id: "' . $this->basketId . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertFalse($result['body']['data']['basketMakePrivate']['public']);
    }
}
