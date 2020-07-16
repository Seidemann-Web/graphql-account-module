<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Customer\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class AddressMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

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

    public function testCustomerInvoiceAddressSet(): void
    {
        $shopId = '2';
        $this->ensureShop((int) $shopId);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            customerInvoiceAddressSet(invoiceAddress: {
                salutation: "MRS"
                firstname: "Jane"
                lastname: "Doe"
                company: "No GmbH"
                additionalInfo: "Invoice address"
                street: "SomeStreet"
                streetNumber: "999"
                zipCode: "10000"
                city: "Any City"
                countryId: "a7c40f631fc920687.20179984"
                phone: "123456"
                mobile: "12345678"
                fax: "555"
            }){
                salutation
                firstname
                lastname
                company
                additionalInfo
                street
                streetNumber
                zipCode
                city
                phone
                mobile
                fax
              }
        }');

        $this->assertResponseStatus(200, $result);

        $this->assertSame([
            'salutation'     => 'MRS',
            'firstname'      => 'Jane',
            'lastname'       => 'Doe',
            'company'        => 'No GmbH',
            'additionalInfo' => 'Invoice address',
            'street'         => 'SomeStreet',
            'streetNumber'   => '999',
            'zipCode'        => '10000',
            'city'           => 'Any City',
            'phone'          => '123456',
            'mobile'         => '12345678',
            'fax'            => '555',
        ], $result['body']['data']['customerInvoiceAddressSet']);
    }

    public function testInvoiceAddressForMallUserFromOtherSubshop(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        $this->assignUserToShop(1);

        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_USER_PASSWORD);

        $result = $this->query('mutation {
            customerInvoiceAddressSet(invoiceAddress: {
                salutation: "MRS"
                firstname: "Janice"
                lastname: "Dodo"
                company: "No GmbH"
                additionalInfo: "Invoice address"
                street: "SomeStreet"
                streetNumber: "999"
                zipCode: "10000"
                city: "Any City"
                countryId: "a7c40f631fc920687.20179984"
                phone: "123456"
                mobile: "12345678"
                fax: "555"
            }){
                firstname
                lastname
              }
        }');

        $this->assertResponseStatus(200, $result);

        $this->assertSame(
            [
                'firstname' => 'Janice',
                'lastname'  => 'Dodo',
            ],
            $result['body']['data']['customerInvoiceAddressSet']
        );
    }

    private function assignUserToShop(int $shopid): void
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);
        $user->assign(
            [
                'oxshopid' => $shopid,
            ]
        );
        $user->save();
    }
}
