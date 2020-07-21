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
    private const TEST_BASKET = 'test_basket';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'admin';

    private const OTHER_PASSWORD = 'admin';

    public function setUpBeforeTestSuite(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $this->query(
            'mutation{
                basketMakePublic(id: "' . self::TEST_BASKET . '"){
                    public
                }
            }'
        );

        parent::setUpBeforeTestSuite();
    }

    public function testMakePrivateBasketNoToken(): void
    {
        $result = $this->query(
            'mutation{
                basketMakePrivate(id: "' . self::TEST_BASKET . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testMakePrivateBasketNotFound(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation{
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
            'mutation{
                basketMakePrivate(id: "' . self::TEST_BASKET . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(403, $result);
    }

    public function testMakePrivateBasketWithToken(): void
    {
        $this->markTestIncomplete('TODO: finish during roundtrip testing create/remove. remove fixture'); //TODO

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation{
                basketMakePrivate(id: "' . self::TEST_BASKET . '"){
                    public
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['basket']['public']);
    }
}
