<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class AddressTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    public function testDeliveryAddressesForNotLoggedInUser(): void
    {
        $result = $this->query('query {
            customerDeliveryAddresses {
                id
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testDeliveryAddressesForLoggedInUser(): void
    {
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
            [
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
            $result['body']['data']['customerDeliveryAddresses']
        );
    }
}
