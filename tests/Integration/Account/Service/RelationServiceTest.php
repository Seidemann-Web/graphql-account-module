<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Service;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class RelationServiceTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const CUSTOMER_ID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    public function testGetInvoiceAddressRelation(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->queryInvoiceAddressRelation();

        $this->assertResponseStatus(200, $result);

        $customer = $result['body']['data']['customer'];
        $this->assertSame(self::CUSTOMER_ID, $customer['id']);
        $this->assertSame(self::USERNAME, $customer['email']);
        $this->assertSame('2', $customer['customerNumber']);
        $this->assertSame('Marc', $customer['firstName']);
        $this->assertSame('Muster', $customer['lastName']);

        $invoiceAddress = $customer['invoiceAddress'];
        $this->assertNotEmpty($invoiceAddress);
        $this->assertSame('MR', $invoiceAddress['salutation']);
        $this->assertSame('Marc', $invoiceAddress['firstName']);
        $this->assertSame('Muster', $invoiceAddress['lastName']);
    }

    public function testGetDeliveryAddressesRelation(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->queryDeliveryAddressesRelation();

        $this->assertResponseStatus(200, $result);

        $customer = $result['body']['data']['customer'];
        $this->assertSame(self::CUSTOMER_ID, $customer['id']);
        $this->assertSame(self::USERNAME, $customer['email']);
        $this->assertSame('2', $customer['customerNumber']);
        $this->assertSame('Marc', $customer['firstName']);
        $this->assertSame('Muster', $customer['lastName']);

        $deliveryAddresses = $customer['deliveryAddresses'];
        $this->assertNotEmpty($deliveryAddresses);
        $this->assertCount(2, $deliveryAddresses);
        [$deliveryAddress1, $deliveryAddress2] = $deliveryAddresses;
        $this->assertSame('MR', $deliveryAddress1['salutation']);
        $this->assertSame('Marc', $deliveryAddress1['firstName']);
        $this->assertSame('Muster', $deliveryAddress1['lastName']);
        $this->assertSame('MR', $deliveryAddress2['salutation']);
        $this->assertSame('Marc', $deliveryAddress2['firstName']);
        $this->assertSame('Muster', $deliveryAddress2['lastName']);
    }

    private function queryInvoiceAddressRelation(): array
    {
        return $this->query('query {
            customer {
                id
                email
                customerNumber
                firstName
                lastName
                invoiceAddress {
                    salutation
                    firstName
                    lastName
                }
            }
        }');
    }

    private function queryDeliveryAddressesRelation(): array
    {
        return $this->query('query {
            customer {
                id
                email
                customerNumber
                firstName
                lastName
                deliveryAddresses {
                    salutation
                    firstName
                    lastName
                }
            }
        }');
    }
}
