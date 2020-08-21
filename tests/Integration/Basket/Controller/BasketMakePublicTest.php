<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketMakePublicTest extends TokenTestCase
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
                    public: false
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

    public function testMakePublicBasketNoToken(): void
    {
        //Reset token set in setUp method
        $this->setAuthToken('');

        $result = $this->query(
            'mutation {
                basketMakePublic(id: "' . $this->basketId . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testMakePublicBasketNotFound(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                basketMakePublic(id: "this_is_no_saved_basket_id"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testMakePublicBasketOfOtherCustomer(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query(
            'mutation {
                basketMakePublic(id: "' . $this->basketId . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(401, $result);
    }

    public function testMakePublicBasketWithToken(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                basketMakePublic(id: "' . $this->basketId . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basketMakePublic']['public']);
    }
}
