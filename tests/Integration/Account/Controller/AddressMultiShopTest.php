<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class AddressMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    public function testCustomerInvoiceAddressSet(): void
    {
        $shopId = '2';
        $this->ensureShop((int) $shopId);

        Registry::getConfig()->setShopId($shopId);
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
                countryID: "a7c40f631fc920687.20179984"
                phone: "123456"
                mobile: "12345678"
                fax: "555"
                creationDate: "2022-10-10"
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
                created
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
            'created'        => '2022-10-10T00:00:00+02:00',
        ], $result['body']['data']['customerInvoiceAddressSet']);
    }
}
