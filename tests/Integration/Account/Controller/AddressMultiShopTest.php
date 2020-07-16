<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Customer\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class AddressMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const DELIVERY_ADDRESS_SHOP_1 = 'test_delivery_address';

    private const DELIVERY_ADDRESS_SHOP_2 = 'test_delivery_address_shop_2';

    public function deliveryAddressesDataProviderPerShop()
    {
        return [
            'shop_1' => [
                'shopid'   => '1',
                'expected' => [
                    [
                        'id'           => self::DELIVERY_ADDRESS_SHOP_1,
                        'firstname'    => 'Marc',
                        'street'       => 'Hauptstr',
                        'streetNumber' => '13',
                    ],
                    [
                        'id'           => 'test_delivery_address_2',
                        'firstName'    => 'Marc',
                        'street'       => 'Hauptstr2',
                        'streetNumber' => '132',
                    ],
                ],
            ],
            'shop_2' => [
                'shopid'   => '2',
                'expected' => [
                    [
                        'id'           => self::DELIVERY_ADDRESS_SHOP_2,
                        'firstname'    => 'Marc2',
                        'street'       => 'Hauptstr2',
                        'streetNumber' => '2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider deliveryAddressesDataProviderPerShop
     *
     * @param string $shopId
     * @param array  $expected
     */
    public function testGetDeliveryAddressesForLoggedInUser($shopId, $expected): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            customerDeliveryAddresses {
                id
                firstName
                street
                streetNumber
            }
        }');

        $this->assertResponseStatus(200, $result);

        $this->assertSame(
            $expected,
            $result['body']['data']['customerDeliveryAddresses']
        );
    }

    /**
     * @dataProvider deliveryAddressDeletionProvider
     */
    public function testDeliveryAddressDeletionPerShop(string $shopId, string $deliveryAddressId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->deleteCustomerDeliveryAddressMutation($deliveryAddressId);

        $this->assertResponseStatus(200, $result);
    }

    public function deliveryAddressDeletionProvider(): array
    {
        return [
            ['1', self::DELIVERY_ADDRESS_SHOP_1],
            ['2', self::DELIVERY_ADDRESS_SHOP_2],
        ];
    }

    /**
     * @dataProvider deliveryAddressDeletionPerDifferentShopProvider
     */
    public function testDeliveryAddressDeletionFromShop1ToShop2(string $shopId, string $deliveryAddressId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->deleteCustomerDeliveryAddressMutation($deliveryAddressId);

        $this->assertResponseStatus(404, $result);
    }

    public function deliveryAddressDeletionPerDifferentShopProvider(): array
    {
        return [
            ['1', self::DELIVERY_ADDRESS_SHOP_2],
            ['2', self::DELIVERY_ADDRESS_SHOP_1],
        ];
    }

    private function deleteCustomerDeliveryAddressMutation(string $deliveryAddressId): array
    {
        return $this->query(
            'mutation {
                customerDeliveryAddressDelete(id: "' . $deliveryAddressId . '")
            }'
        );
    }
}
