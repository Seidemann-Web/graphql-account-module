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

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const DELIVERY_ADDRESS_ID = 'test_delivery_address';

    private const DELIVERY_ADDRESS_ID_2 = 'test_delivery_address_2';

    public function testGetDeliveryAddressesForNotLoggedInUser(): void
    {
        $result = $this->query('query {
            customerDeliveryAddresses {
                id
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testGetDeliveryAddressesForLoggedInUser(): void
    {
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
            [
                [
                    'id'           => 'test_delivery_address',
                    'firstName'    => 'Marc',
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
            $result['body']['data']['customerDeliveryAddresses']
        );
    }

    public function testDeliveryAddressDeletionWithoutToken(): void
    {
        $result = $this->deleteCustomerDeliveryAddressMutation(self::DELIVERY_ADDRESS_ID);

        $this->assertResponseStatus(400, $result);
    }

    public function testDeliveryAddressDeletionForDifferentCustomer(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);

        $result = $this->deleteCustomerDeliveryAddressMutation(self::DELIVERY_ADDRESS_ID);

        $this->assertResponseStatus(401, $result);
    }

    public function testDeliveryAddressDeletionWithNonExistingId(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);

        $result = $this->deleteCustomerDeliveryAddressMutation('non-existing-id');

        $this->assertResponseStatus(404, $result);
    }

    public function testDeliveryAddressDeletionWithToken(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->deleteCustomerDeliveryAddressMutation(self::DELIVERY_ADDRESS_ID);

        $this->assertResponseStatus(200, $result);
    }

    public function testDeliveryAddressDeletionFromAdmin(): void
    {
        $this->prepareToken();

        $result = $this->deleteCustomerDeliveryAddressMutation(self::DELIVERY_ADDRESS_ID_2);

        $this->assertResponseStatus(200, $result);
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
