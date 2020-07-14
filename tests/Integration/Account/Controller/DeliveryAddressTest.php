<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class DeliveryAddressTest extends TokenTestCase
{
    private const DIFFERENT_USERNAME = 'differentuser@oxid-esales.com';

    private const DIFFERENT_PASSWORD = 'useruser';

    private const DIFFERENT_USER_OXID = '_45ad3b5380202966df6ff128e9eecaq';

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxaddress', 'oxuserid');

        parent::tearDown();
    }

    public function testAddDeliveryAddressForNotLoggedInUser(): void
    {
        $result = $this->query(
            'mutation {
                deliveryAddressAdd(deliveryAddress: {
                    salutation: "MR",
                    firstname: "Max",
                    lastname: "Mustermann"
                })
                {
                   firstname
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testAddDeliveryAddressForLoggedInUserMissingInput(): void
    {
        $this->prepareToken(self::DIFFERENT_USERNAME, self::DIFFERENT_PASSWORD);

        $inputFields =  [
            'salutation'     => 'MR',
            'firstname'      => 'Marc',
            'lastname'       => 'Muster',
            'company'        => 'No GmbH',
            'additionalInfo' => 'private delivery',
            'street'         => 'Bertoldstrasse',
            'streetNumber'   => '48',
            'zipCode'        => '79098',
            'city'           => 'Freiburg',
            'countryId'      => 'a7c40f631fc920687.20179984',
            'phone'          => '1234',
            'fax'            => '4321',
        ];

        $queryPart = '';

        foreach ($inputFields as $key => $value) {
            $queryPart .= $key . ': "' . $value . '",' . PHP_EOL;
        }

        $result = $this->query(
            'mutation {
                deliveryAddressAdd(deliveryAddress: {' .
                $queryPart .
                '})
                {
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
                    fax
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $deliveryAddress = $result['body']['data']['deliveryAddressAdd'];

        foreach ($inputFields as $key => $value) {
            $this->assertSame($value, $deliveryAddress[$key]);
        }
    }
}
