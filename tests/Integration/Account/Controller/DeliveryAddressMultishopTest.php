<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Customer\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class DeliveryAddressMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const PRIMARY_SHOP_USERNAME = 'user@oxid-esales.com';

    private const PRIMARY_SHOP_PASSWORD = 'useruser';

    private const PRIMARY_SHOP_USER_OXID = '123ad3b5380202966df6ff128e9eecaq';

    public function dataProviderDeliveryAddressPerShop()
    {
        return [
            'shop_1' => [
                'shopid'   => '1',
            ],
            'shop_2' => [
                'shopid'   => '2',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderDeliveryAddressPerShop
     */
    public function testDeliveryAddressPerShopForMallUser(string $shopId): void
    {
        $this->ensureShop((int) $shopId);
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->executeMutation();

        $this->assertResponseStatus(200, $result);
        $this->assertNotEmpty($result['body']['data']['deliveryAddressAdd']['id']);
    }

    private function executeMutation(): array
    {
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

        return $this->query(
            'mutation {
                deliveryAddressAdd(deliveryAddress: {' .
            $queryPart .
            '})
                {
                    id
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
                    country {
                        id
                    }
                }
            }'
        );
    }
}
