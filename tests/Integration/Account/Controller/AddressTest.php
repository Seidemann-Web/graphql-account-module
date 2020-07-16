<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class AddressTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const DELIVERY_ADDRESS_ID = 'test_delivery_address';

    private const DELIVERY_ADDRESS_ID_2 = 'test_delivery_address_2';

    private const DIFFERENT_USERNAME = 'differentuser@oxid-esales.com';

    private const DIFFERENT_PASSWORD = 'useruser';

    /**
     * @var array
     */
    private $defaultMustFillFields;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultMustFillFields = EshopRegistry::getConfig()->getConfigParam('aMustFillFields');
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        EshopRegistry::getConfig()->setConfigParam('aMustFillFields', $this->defaultMustFillFields);

        parent::tearDown();
    }

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

    public function customerInvoiceAddressProvider(): array
    {
        return [
            [
                [
                    'salutation'     => 'Mrs.',
                    'firstName'      => 'First',
                    'lastName'       => 'Last',
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
                    'firstName'      => 'Invoice First',
                    'lastName'       => 'Invoice Last',
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
                    firstName: "' . $invoiceData['firstName'] . '"
                    lastName: "' . $invoiceData['lastName'] . '"
                    company: "' . $invoiceData['company'] . '"
                    additionalInfo: "' . $invoiceData['additionalInfo'] . '"
                    street: "' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "' . $invoiceData['city'] . '"
                    countryId: "' . $invoiceData['country']['id'] . '"
                    vatID: "' . $invoiceData['vatID'] . '"
                    phone: "' . $invoiceData['phone'] . '"
                    mobile: "' . $invoiceData['mobile'] . '"
                    fax: "' . $invoiceData['fax'] . '"
                }
            ){
                salutation
                firstName
                lastName
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
                    'firstName'      => 'First',
                    'lastName'       => 'Last',
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
                    'firstName'      => 'Invoice First',
                    'lastName'       => 'Invoice Last',
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
                    firstName: "' . $invoiceData['firstName'] . '"
                    lastName: "' . $invoiceData['lastName'] . '"
                    street: "' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "' . $invoiceData['city'] . '"
                    countryId: "' . $invoiceData['country']['id'] . '"
                }
            ){
                salutation
                firstName
                lastName
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
                    firstName: "' . $invoiceData['firstName'] . '"
                    lastName: "' . $invoiceData['lastName'] . '"
                    company: "Invoice ' . $invoiceData['company'] . '"
                    additionalInfo: "Invoice address additional ' . $invoiceData['additionalInfo'] . '"
                    street: "Invoice ' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "Invoice ' . $invoiceData['city'] . '"
                    countryId: "a7c40f631fc920687.' . $invoiceData['country']['id'] . '"
                    vatID: "' . $invoiceData['vatID'] . '"
                    phone: "' . $invoiceData['phone'] . '"
                    mobile: "' . $invoiceData['mobile'] . '"
                    fax: "' . $invoiceData['fax'] . '"
                }
            ){
                salutation
                firstName
                lastName
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
                    'firstName'      => '',
                    'lastName'       => '',
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
                    'firstName'      => 'First',
                    'lastName'       => 'Last',
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
                    firstName: "' . $invoiceData['firstName'] . '"
                    lastName: "' . $invoiceData['lastName'] . '"
                    street: "' . $invoiceData['street'] . '"
                    streetNumber: "' . $invoiceData['streetNumber'] . '"
                    zipCode: "' . $invoiceData['zipCode'] . '"
                    city: "' . $invoiceData['city'] . '"
                    countryId: "' . $invoiceData['country']['id'] . '"
                }
            ){
                salutation
                firstName
                lastName
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

    public function providerRequiredFields()
    {
        return [
            'set1' => [
                'fields' => [
                    'oxuser__oxfname',
                    'oxuser__oxlname',
                    'oxuser__oxstreet',
                    'oxuser__oxstreetnr',
                    'oxuser__oxzip',
                    'oxuser__oxcity',
                    'oxuser__oxcountryid',
                ],
            ],
            'set2' => [
                'fields' => [
                    'oxuser__oxfname',
                    'oxuser__oxlname',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerRequiredFields
     */
    public function testAddDeliveryAddressForLoggedInUserMissingInput(array $mustFillFields): void
    {
        EshopRegistry::getConfig()->setConfigParam('aMustFillFields', $mustFillFields);
        $this->prepareToken(self::DIFFERENT_USERNAME, self::DIFFERENT_PASSWORD);

        $result = $this->query(
            'mutation {
                customerInvoiceAddressSet(invoiceAddress: {' .
            '})
                {
                    salutation
                }
            }'
        );

        $expected = [];

        foreach ($mustFillFields as $field) {
            $tmp             = explode('__', $field);
            $name            = ltrim($tmp[1], 'ox');
            $expected[$name] = $name;
        }
        $expected = rtrim(implode(', ', $expected), ', ');

        $this->assertResponseStatus(400, $result);
        $this->assertContains($expected, $result['body']['errors'][0]['message']);
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
