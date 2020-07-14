<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use DateTime;
use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class CustomerTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const USER_OXID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');

        parent::tearDown();
    }

    public function testCustomerForNotLoggedInUser(): void
    {
        $result = $this->query('query {
            customer {
               id
               firstName
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testCustomerForLoggedInUser(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            customer {
               id
               firstName
               lastName
               email
               customerNumber
               birthdate
               points
               registered
               created
               updated
            }
        }');

        $this->assertResponseStatus(200, $result);

        $customerData = $result['body']['data']['customer'];

        $this->assertEquals(self::USER_OXID, $customerData['id']);
        $this->assertEquals('Marc', $customerData['firstName']);
        $this->assertEquals('Muster', $customerData['lastName']);
        $this->assertEquals(self::USERNAME, $customerData['email']);
        $this->assertEquals('2', $customerData['customerNumber']);
        $this->assertSame(0, $customerData['points']);
        $this->assertSame('1984-12-21T00:00:00+01:00', $customerData['birthdate']);
        $this->assertSame('2011-02-01T08:41:25+01:00', $customerData['registered']);
        $this->assertSame('2011-02-01T08:41:25+01:00', $customerData['created']);
        $this->assertInstanceOf(DateTime::class, DateTime::createFromFormat(DateTime::ATOM, $customerData['updated']));
    }

    public function testCustomerNewsletterStatusNoEntryInDatabase(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            customer {
                id
                firstName
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('Marc', $result['body']['data']['customer']['firstName']);
        $this->assertNull($result['body']['data']['customer']['newsletterStatus']);
    }

    public function testCustomerNewsletterStatusInvalidEntryInDatabase(): void
    {
        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->setId('_othertestuser');
        $subscription->assign(
            [
                'oxuserid'  => self::OTHER_USER_OXID,
                'oxdboptin' => 6,
            ]
        );
        $subscription->save();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            customer {
                id
                firstName
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('UNSUBSCRIBED', $result['body']['data']['customer']['newsletterStatus']['status']);
    }

    public function testCustomerAndNewsletterStatusForUser(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            customer {
                id
                firstName
                newsletterStatus {
                    salutation
                    firstname
                    lastname
                    email
                    status
                    failedEmailCount
                    subscribed
                    unsubscribed
                    updated
                }
            }
        }');

        $this->assertResponseStatus(200, $result);

        $expected = [
            'salutation'       => 'MR',
            'firstname'        => 'Marc',
            'lastname'         => 'Muster',
            'email'            => self::USERNAME,
            'status'           => 'SUBSCRIBED',
            'failedEmailCount' => 0,
            'subscribed'       => '2020-04-01T11:11:11+02:00',
            'unsubscribed'     => null,
        ];

        $this->assertContains('T', $result['body']['data']['customer']['newsletterStatus']['updated']);
        unset($result['body']['data']['customer']['newsletterStatus']['updated']);

        $this->assertEquals(
            $expected,
            $result['body']['data']['customer']['newsletterStatus']
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
                    'salutation'     => 'Mr.',
                    'firstname'      => 'Invoice First',
                    'lastname'       => 'Invoice Last',
                    'company'        => '',
                    'additionalInfo' => '',
                    'street'         => 'Invoice street',
                    'streetNumber'   => '123',
                    'zipCode'        => '3210',
                    'city'           => 'Invoice city',
                    'country'        => [
                        'id'    => '', // no country
                        'title' => '',
                    ],
                    'vatID'  => '',
                    'phone'  => '',
                    'mobile' => '',
                    'fax'    => '',
                ],
                'expectedStatus' => 404,
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
