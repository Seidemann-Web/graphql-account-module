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

    public function customerInvoiceAddressProvider(): array
    {
        return [
            [
                [
                    'salutation'     => 'Mrs.',
                    'firstname'      => 'First',
                    'lastname'       => 'Last',
                    'company'        => '',
                    'additionalInfo' => '',
                    'street'         => 'Invoice street',
                    'streetNumber'   => '123',
                    'zipCode'        => '3210',
                    'city'           => 'Invoice city',
                    'country'        => [
                        'id'    => 'a7c40f6321c6f6109.43859248',
                        'title' => 'Schweiz',
                    ],
                    'vatID'  => '',
                    'phone'  => '',
                    'mobile' => '',
                    'fax'    => '',
                ],
            ],
            [
                [
                    'salutation'     => 'Mr.',
                    'firstname'      => 'Invoice First',
                    'lastname'       => 'Invoice Last',
                    'company'        => 'Invoice Company',
                    'additionalInfo' => 'Invoice address additional info',
                    'street'         => 'Another invoice street',
                    'streetNumber'   => '123',
                    'zipCode'        => '3210',
                    'city'           => 'Another invoice city',
                    'country'        => [
                        'id'    => 'a7c40f631fc920687.20179984',
                        'title' => 'Deutschland',
                    ],
                    'vatID'  => '0987654321',
                    'phone'  => '1234567890',
                    'mobile' => '01234567890',
                    'fax'    => '12345678900',
                ],
            ],
        ];
    }

    /**
     * @dataProvider customerInvoiceAddressProvider
     */
    public function testCustomerInvoiceAddressSet(array $invoiceData): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            customerInvoiceAddressSet (
                invoiceAddress: {
                    salutation: "' . $invoiceData['salutation'] . '"
                    firstname: "' . $invoiceData['firstname'] . '"
                    lastname: "' . $invoiceData['lastname'] . '"
                    company: "' . $invoiceData['company'] . '"
                    additionalInfo: "' . $invoiceData['additionalInfo'] . '"
                    street: "' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "' . $invoiceData['city'] . '"
                    countryID: "' . $invoiceData['country']['id'] . '"
                    vatID: "' . $invoiceData['vatID'] . '"
                    phone: "' . $invoiceData['phone'] . '"
                    mobile: "' . $invoiceData['mobile'] . '"
                    fax: "' . $invoiceData['fax'] . '"
                    creationDate: "2020-07-14"
                }
            ){
                salutation
                firstname
                lastname
                company
                additionalInfo
                street
                streetNumber
                zipCode
                city
                country {
                    id
                    title
                }
                vatID
                phone
                mobile
                fax
            }
        }');

        $this->assertResponseStatus(200, $result);

        $actual = $result['body']['data']['customerInvoiceAddressSet'];
        $this->assertEquals($invoiceData, $actual);
    }

    public function customerInvoiceAddressPartialProvider(): array
    {
        return [
            [
                [
                    'salutation'     => 'Mrs.',
                    'firstname'      => 'First',
                    'lastname'       => 'Last',
                    'company'        => 'Invoice Company',
                    'additionalInfo' => 'Invoice address additional info',
                    'street'         => 'Invoice street',
                    'streetNumber'   => '123',
                    'zipCode'        => '3210',
                    'city'           => 'Invoice city',
                    'country'        => [
                        'id'    => 'a7c40f631fc920687.20179984',
                        'title' => 'Deutschland',
                    ],
                    'vatID'  => '0987654321',
                    'phone'  => '',
                    'mobile' => '',
                    'fax'    => '12345678900',
                ],
            ],
            [
                [
                    'salutation'     => 'Mr.',
                    'firstname'      => 'Invoice First',
                    'lastname'       => 'Invoice Last',
                    'company'        => 'Invoice Company',
                    'additionalInfo' => 'Invoice address additional info',
                    'street'         => 'Another invoice street',
                    'streetNumber'   => '123',
                    'zipCode'        => '3210',
                    'city'           => 'Another invoice city',
                    'country'        => [
                        'id'    => 'a7c40f6321c6f6109.43859248',
                        'title' => 'Schweiz',
                    ],
                    'vatID'  => '0987654321',
                    'phone'  => '',
                    'mobile' => '',
                    'fax'    => '12345678900',
                ],
            ],
        ];
    }

    /**
     * @dataProvider customerInvoiceAddressPartialProvider
     */
    public function testCustomerInvoiceAddressSetWithoutOptionals(array $invoiceData): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            customerInvoiceAddressSet (
                invoiceAddress: {
                    salutation: "' . $invoiceData['salutation'] . '"
                    firstname: "' . $invoiceData['firstname'] . '"
                    lastname: "' . $invoiceData['lastname'] . '"
                    street: "' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "' . $invoiceData['city'] . '"
                    countryID: "' . $invoiceData['country']['id'] . '"
                    creationDate: "2020-07-14"
                }
            ){
                salutation
                firstname
                lastname
                company
                additionalInfo
                street
                streetNumber
                zipCode
                city
                country {
                    id
                    title
                }
                vatID
                phone
                mobile
                fax
            }
        }');

        $this->assertResponseStatus(200, $result);

        $actual = $result['body']['data']['customerInvoiceAddressSet'];
        $this->assertEquals($invoiceData, $actual);
    }

    /**
     * @dataProvider customerInvoiceAddressProvider
     */
    public function testCustomerInvoiceAddressSetNotLoggedIn(array $invoiceData): void
    {
        $result = $this->query('mutation {
            customerInvoiceAddressSet (
                invoiceAddress {
                    salutation: "' . $invoiceData['salutation'] . '"
                    firstname: "' . $invoiceData['firstname'] . '"
                    lastname: "' . $invoiceData['lastname'] . '"
                    company: "Invoice ' . $invoiceData['company'] . '"
                    additionalInfo: "Invoice address additional ' . $invoiceData['additionalInfo'] . '"
                    street: "Invoice ' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "Invoice ' . $invoiceData['city'] . '"
                    countryID: "a7c40f631fc920687.' . $invoiceData['country']['id'] . '"
                    vatID: "' . $invoiceData['vatID'] . '"
                    phone: "' . $invoiceData['phone'] . '"
                    mobile: "' . $invoiceData['mobile'] . '"
                    fax: "' . $invoiceData['fax'] . '"
                    creationDate: "2020-07-14"
                }
            ){
                salutation
                firstname
                lastname
                company
                additionalInfo
                street
                streetNumber
                zipCode
                city
                country {
                    id
                    title
                }
                vatID
                phone
                mobile
                fax
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function customerInvoiceAddressValidationFailProvider(): array
    {
        return [
            [
                'invoiceData' => [
                    'salutation'     => '',
                    'firstname'      => '',
                    'lastname'       => '',
                    'company'        => '',
                    'additionalInfo' => '',
                    'street'         => '',
                    'streetNumber'   => '',
                    'zipCode'        => '',
                    'city'           => '',
                    'country'        => [
                        'id'    => '',
                        'title' => '',
                    ],
                    'vatID'  => '',
                    'phone'  => '',
                    'mobile' => '',
                    'fax'    => '',
                ],
                'expectedStatus' => 400,
            ],
            [
                'invoiceData' => [
                    'salutation'     => 'Mrs.',
                    'firstname'      => 'First',
                    'lastname'       => 'Last',
                    'company'        => '',
                    'additionalInfo' => '',
                    'street'         => 'Another invoice street',
                    'streetNumber'   => '123',
                    'zipCode'        => '3210',
                    'city'           => 'Another invoice city',
                    'country'        => [
                        'id'    => '8f241f1109621faf8.40135556', // invalid country
                        'title' => 'Philippinen',
                    ],
                    'vatID'  => '',
                    'phone'  => '',
                    'mobile' => '',
                    'fax'    => '',
                ],
                'expectedStatus' => 401,
            ],
            [
                'invoiceData' => [
                    'salutation'     => 'Mrs.',
                    'company'        => '',
                    'additionalInfo' => '',
                    'city'           => 'Another invoice city',
                    'country'        => [
                        'id'    => '8f241f1109621faf8.40135556', // invalid country
                        'title' => 'Philippinen',
                    ],
                ],
                'expectedStatus' => 400,
            ],
        ];
    }

    /**
     * @dataProvider customerInvoiceAddressValidationFailProvider
     */
    public function testCustomerInvoiceAddressSetValidationFail(array $invoiceData, int $expectedStatus): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            customerInvoiceAddressSet (
                invoiceAddress: {
                    salutation: "' . $invoiceData['salutation'] . '"
                    firstname: "' . $invoiceData['firstname'] . '"
                    lastname: "' . $invoiceData['lastname'] . '"
                    street: "' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "' . $invoiceData['city'] . '"
                    countryID: "' . $invoiceData['country']['id'] . '"
                    creationDate: "2020-07-14"
                }
            ){
                salutation
                firstname
                lastname
                company
                additionalInfo
                street
                streetNumber
                zipCode
                city
                country {
                    title
                }
                vatID
                phone
                mobile
                fax
            }
        }');

        $this->assertResponseStatus($expectedStatus, $result);
    }
}
