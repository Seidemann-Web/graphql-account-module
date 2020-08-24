<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class CustomerOrderHistoryTest extends TokenTestCase
{
    private const DIFFERENT_USERNAME = 'differentuser@oxid-esales.com';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const ORDER_WITH_ALL_DATA = '8c726d3f42ff1a6ea2828d5f309de881';

    public function testCustomerOrderWithAllDataById(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query {
                customer {
                    id
                    orders(
                        pagination: {limit: 1, offset: 0}
                    ){
                        id
                        orderNumber
                        invoiceAddress {
                            salutation
                            email
                            firstName
                            lastName
                            company
                            additionalInfo
                            street
                            streetNumber
                            zipCode
                            city
                            vatID
                            phone
                            fax
                        }
                        deliveryAddress {
                            salutation
                            firstName
                            lastName
                            company
                            additionalInfo
                            street
                            streetNumber
                            zipCode
                            city
                            phone
                            fax
                            country {
                                id
                                title
                            }
                            state {
                                id
                                title
                            }
                        }
                        invoiceNumber
                        invoiced
                        remark
                        currency {
                            name
                        }
                        cancelled
                        ordered
                        paid
                        updated
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals(1, count($result['body']['data']['customer']['orders']));

        $order = $result['body']['data']['customer']['orders'][0];
        $this->assertSame(self::ORDER_WITH_ALL_DATA, $order['id']);
        $this->assertSame(4, $order['orderNumber']);
        $this->assertSame(665, $order['invoiceNumber']);
        $this->assertSame('2020-08-24T00:00:00+02:00', $order['invoiced']);
        $this->assertSame('please deliver as fast as you can', $order['remark']);
        $this->assertSame('EUR', $order['currency']['name']);
        $this->assertFalse($order['cancelled']);
        $this->assertSame('2020-05-23T14:08:55+02:00', $order['ordered']);
        $this->assertNull($order['paid']);
        $this->assertNotEmpty($order['updated']);

        $this->assertInvoiceAddress($order['invoiceAddress']);
        $this->assertDeliveryAddress($order['deliveryAddress']);
    }

    public function testCustomerOrderWithoutDeliveryAddress(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query {
                customer {
                    orders(
                      pagination: {limit: 1, offset: 3}
                    )
                    {
                        id
                        orderNumber
                        deliveryAddress {
                            firstName
                            lastName
                        }
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals(1, count($result['body']['data']['customer']['orders']));

        $this->assertSame(1, $result['body']['data']['customer']['orders'][0]['orderNumber']);
        $this->assertNull($result['body']['data']['customer']['order']['deliveryAddress']);
    }

    public function testCustomerOrders(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query {
                customer {
                    orders {
                        id
                        orderNumber
                        deliveryAddress {
                            firstName
                            lastName
                        }
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals(4, count($result['body']['data']['customer']['orders']));
    }

    public function providerCustomerOrdersPagination()
    {
        return [
            'set1' => [
                'pagination'    => '',
                'expected'      => 14,
                'first_ordernr' => '113',
                'last_ordernr'  => '100',
            ],
            'set2' => [
                'pagination'    => '(pagination: {limit: 1, offset: 0})',
                'expected'      => 1,
                'first_ordernr' => '113',
                'last_ordernr'  => '113',
            ],
            'set3' => [
                'pagination'    => '(pagination: {limit: 10, offset: 0})',
                'expected'      => 10,
                'first_ordernr' => '113',
                'last_ordernr'  => '104',
            ],
            'set4' => [
                'pagination'    => '(pagination: {limit: 1, offset: 10})',
                'expected'      => 1,
                'first_ordernr' => '103',
                'last_ordernr'  => '103',
            ],
            'set5' => [
                'pagination'    => '(pagination: {limit: 1, offset: 100})',
                'expected'      => 0,
                'first_ordernr' => '',
                'last_ordernr'  => '',
            ],
        ];
    }

    /**
     * @dataProvider providerCustomerOrdersPagination
     */
    public function testCustomerOrdersPagination(
        string $pagination,
        int $expected,
        string $firstOrderNr,
        string $lastOrderNr
    ): void {
        $this->prepareToken(self::DIFFERENT_USERNAME, self::PASSWORD);

        $query = 'query {
                customer {
                    orders %s
                    {
                        id
                        orderNumber
                    }
                }
            }';

        $result = $this->query(sprintf($query, $pagination));

        $this->assertResponseStatus(200, $result);
        $this->assertEquals($expected, count($result['body']['data']['customer']['orders']));

        if (!empty($firstOrderNr)) {
            $this->assertEquals($firstOrderNr, $result['body']['data']['customer']['orders'][0]['orderNumber']);
            $this->assertEquals($lastOrderNr, array_pop($result['body']['data']['customer']['orders'])['orderNumber']);
        }
    }

    private function assertInvoiceAddress(array $address): void
    {
        $expected = [
            'email'          => 'billuser@oxid-esales.com',
            'salutation'     => 'MR',
            'firstName'      => 'Marc',
            'lastName'       => 'Muster',
            'company'        => 'bill company',
            'additionalInfo' => 'additional bill info',
            'street'         => 'Hauptstr.',
            'streetNumber'   => '13',
            'zipCode'        => '79098',
            'city'           => 'Freiburg',
            'vatID'          => 'bill vat id',
            'phone'          => '1234',
            'fax'            => '4567',
        ];

        if ($this->getConfig()->getEdition() !== 'EE') {
            $expected['vatID'] = '';
        }

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $address[$key], $key);
        }
    }

    private function assertDeliveryAddress(array $address): void
    {
        $expected = [
            'salutation'     => 'MRS',
            'firstName'      => 'Marcia',
            'lastName'       => 'Pattern',
            'company'        => 'del company',
            'additionalInfo' => 'del addinfo',
            'street'         => 'Nebenstraße',
            'streetNumber'   => '123',
            'zipCode'        => '79106',
            'city'           => 'Freiburg',
            'phone'          => '04012345678',
            'fax'            => '04012345679',
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $address[$key], $key);
        }
    }
}
