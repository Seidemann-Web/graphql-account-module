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

    public function deliveryAddressesDataProviderPerShop()
    {
        return [
            'shop_1' => [
                'shopid'   => '1',
                'expected' => [
                    [
                        'id'           => 'test_delivery_address',
                        'firstname'    => 'Marc',
                        'street'       => 'Hauptstr',
                        'streetNumber' => '13',
                    ],
                    [
                        'id'           => 'test_delivery_address_2',
                        'firstname'    => 'Marc',
                        'street'       => 'Hauptstr2',
                        'streetNumber' => '132',
                    ],
                ],
            ],
            'shop_2' => [
                'shopid'   => '2',
                'expected' => [],
            ],
        ];
    }

    /**
     * @dataProvider deliveryAddressesDataProviderPerShop
     *
     * @param string $shopId
     * @param array  $expected
     */
    public function testDeliveryAddressesForLoggedInUser($shopId, $expected): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            customerDeliveryAddresses {
                id
                firstname
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
}
