<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class InvoiceAddressTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    public function testInvoiceAddressForNotLoggedInUser(): void
    {
        $result = $this->query('query {
            customerInvoiceAddress {
                firstName
                lastName
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testInvoiceAddressForLoggedInUser(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            customerInvoiceAddress {
                salutation
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
                mobile
                fax
            }
        }');

        $this->assertResponseStatus(200, $result);

        $this->assertSame(
            [
                'salutation'     => 'MR',
                'firstName'      => 'Marc',
                'lastName'       => 'Muster',
                'company'        => '',
                'additionalInfo' => '',
                'street'         => 'Hauptstr.',
                'streetNumber'   => '13',
                'zipCode'        => '79098',
                'city'           => 'Freiburg',
                'vatID'          => '',
                'phone'          => '',
                'mobile'         => '',
                'fax'            => '',
            ],
            $result['body']['data']['customerInvoiceAddress']
        );
    }
}
