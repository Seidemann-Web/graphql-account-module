<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishedPrice\Controller;

use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class WishedPriceTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';
    private const PASSWORD = 'useruser';

    private const WISHED_PRICE = '_test_wished_price_1_'; // Belongs to user@oxid-esales.com
    private const WISHED_PRICE_2 = '_test_wished_price_6_'; // Belongs to user@oxid-esales.com
    private const WISHED_PRICE_WITH_INACTIVE_PRODUCT = '_test_wished_price_4_';
    private const WISHED_PRICE_WITH_NON_EXISTING_PRODUCT = '_test_wished_price_5_';
    private const WISHED_PRICE_WITH_DISABLED_WISHED_PRICE_FOR_PRODUCT = '_test_wished_price_3_';
    private const WISHED_PRICE_WITHOUT_USER = '_test_wished_price_without_user_';
    private const WISHED_PRICE_ASSIGNED_TO_OTHER_USER = '_test_wished_price_2_'; // Belongs to otheruser@oxid-esales.com
    private const WISHED_PRICE_WITH_NON_EXISTING_USER = '_test_wished_price_7_';
    private const WISHED_PRICE_TO_BE_DELETED = '_test_wished_price_delete_';

    public function testGetWishedPrice()
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                wishedPrice(id: "' . self::WISHED_PRICE . '") {
                    product {
                        title
                    }
                    price {
                        price
                    }
                    currency {
                        name
                    }
                    id
                    email
                    notificationDate
                    creationDate
                    user {
                        userName
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $wishedPrice = $result['body']['data']['wishedPrice'];
        $this->assertEquals($wishedPrice['product']['title'], 'Kuyichi Ledergürtel JEVER');
        $this->assertEquals($wishedPrice['price']['price'], '10.0');
        $this->assertEquals($wishedPrice['currency']['name'], 'EUR');
        $this->assertEquals($wishedPrice['id'], self::WISHED_PRICE);
        $this->assertEquals($wishedPrice['email'], self::USERNAME);
        $this->assertEquals($wishedPrice['user']['userName'], self::USERNAME);
        $this->assertNull($wishedPrice['notificationDate']);

        $this->assertEmpty(array_diff(array_keys($wishedPrice), [
            'product',
            'price',
            'currency',
            'id',
            'email',
            'notificationDate',
            'creationDate',
            'user'
        ]));
    }

    public function testGetWishedPriceNotificationDate()
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                wishedPrice(id: "' . self::WISHED_PRICE_2 . '") {
                    notificationDate
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $wishedPrice = $result['body']['data']['wishedPrice'];
        $this->assertNotNull($wishedPrice['notificationDate']);
    }

    public function testGetWishedPriceWithoutToken()
    {
        $result = $this->query(
            'query{
                wishedPrice(id: "' . self::WISHED_PRICE . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(401, $result);
    }

    public function dataProviderWishedPrices404and401()
    {
        return [
            [self::WISHED_PRICE_WITHOUT_USER, 401],
            [self::WISHED_PRICE_ASSIGNED_TO_OTHER_USER, 401],
            [self::WISHED_PRICE_WITH_INACTIVE_PRODUCT, 401],
            [self::WISHED_PRICE_WITH_DISABLED_WISHED_PRICE_FOR_PRODUCT, 404],
            [self::WISHED_PRICE_WITH_NON_EXISTING_PRODUCT, 404],
            [self::WISHED_PRICE_WITH_NON_EXISTING_USER, 401],
        ];
    }

    /**
     * @dataProvider dataProviderWishedPrices404and401
     */
    public function testWishedPricesWithResponse404and401(string $id, int $status)
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query{
                wishedPrice(id: "' . $id . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus($status, $result);
    }

    public function dataProviderWishedPricesWithAuthorization()
    {
        return [
            [self::WISHED_PRICE_WITHOUT_USER],
            [self::WISHED_PRICE_ASSIGNED_TO_OTHER_USER],
            [self::WISHED_PRICE_WITH_INACTIVE_PRODUCT],
            [self::WISHED_PRICE_WITH_DISABLED_WISHED_PRICE_FOR_PRODUCT],
            [self::WISHED_PRICE_WITH_NON_EXISTING_USER],
        ];
    }

    /**
     * @dataProvider dataProviderWishedPricesWithAuthorization
     */
    public function testWishedPricesWithAuthorization(string $id)
    {
        $this->prepareToken();

        $result = $this->query(
            'query{
                wishedPrice(id: "' . $id . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
    }

    public function testDeleteWishedPriceWithoutToken()
    {
        $result = $this->query(
            'mutation {
                wishedPriceDelete(id: "' . self::WISHED_PRICE_TO_BE_DELETED . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(401, $result);
    }

    public function providerDeleteWishedPrice()
    {
        return [
            'admin' => [
                'username' => 'admin',
                'password' => 'admin',
                'oxid'     => self::WISHED_PRICE_TO_BE_DELETED . '1_',
                'expected' => 200
            ],
            'user'  => [
                'username' => 'user@oxid-esales.com',
                'password' => 'useruser',
                'oxid'     => self::WISHED_PRICE_TO_BE_DELETED . '2_',
                'expected' => 200
            ],
            'otheruser'  => [
                'username' => 'otheruser@oxid-esales.com',
                'password' => 'useruser',
                'oxid'     => self::WISHED_PRICE_TO_BE_DELETED . '3_',
                'expected' => 401
            ]
        ];
    }

    /**
     * @dataProvider providerDeleteWishedPrice
     */
    public function testDeleteWishedPriceWithToken(string $username, string $password, string $oxid, int $expected)
    {
        $this->prepareToken($username, $password);

        //wished price in question belongs to user@oxid-esales.com.
        //so admin and this user should be able to delete the wished price, otheruser not.
        $result = $this->query(
            'mutation {
                wishedPriceDelete(id: "' . $oxid . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus($expected, $result);

        if (200 == $expected) {
            $this->assertEquals($oxid, $result['body']['data']['wishedPriceDelete']['id']);
        }
    }

    /**
     * @dataProvider providerDeleteWishedPrice
     */
    public function testDeleteNonExistingWishedPrice(string $username, string $password)
    {
        $this->prepareToken($username, $password);

        $result = $this->query(
            'mutation {
                wishedPriceDelete(id: "non_existing_wished_price") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function providerWishedPrices()
    {
        return [
            'admin' => [
                'username' => 'admin',
                'password' => 'admin',
                'expected' => 200
            ],
            'user'  => [
                'username' => 'user@oxid-esales.com',
                'password' => 'useruser',
                'expected' => 200
            ],
            'otheruser'  => [
                'username' => 'otheruser@oxid-esales.com',
                'password' => 'useruser',
                'expected' => 401
            ]
        ];
    }

    /**
     * @dataProvider providerWishedPrices
     */
    public function testWishedPrices(string $username, string $password, int $expectedResponse)
    {
        $this->prepareToken($username, $password);

        //wished price in question belongs to user@oxid-esales.com.
        //so admin and this user should be able to delete the wished price, otheruser not.
        $result = $this->query(
            'query {
                wishedPrices {
                    id
                }
            }'
        );

        $this->assertResponseStatus($expectedResponse, $result);

        if (200 == $expectedResponse) {
            $this->assertEquals(5, $result['body']['data']['wishedPrices']);
        }
    }
}
