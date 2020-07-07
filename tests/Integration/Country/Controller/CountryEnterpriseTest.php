<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Country\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class CountryEnterpriseTest extends MultishopTestCase
{
    private const ACTIVE_COUNTRY = 'a7c40f631fc920687.20179984';

    public function providerGetCountryMultilanguage()
    {
        return [
            'shop_1_de' => [
                'shopId'     => '1',
                'languageId' => '0',
                'title'      => 'Deutschland',
            ],
            'shop_1_en' => [
                'shopId'     => '1',
                'languageId' => '1',
                'title'      => 'Germany',
            ],
            'shop_2_de' => [
                'shopId'     => '2',
                'languageId' => '0',
                'title'      => 'Deutschland',
            ],
            'shop_2_en' => [
                'shopId'     => '2',
                'languageId' => '1',
                'title'      => 'Germany',
            ],
        ];
    }

    /**
     * Check multishop multilanguage data is accessible
     *
     * @dataProvider providerGetCountryMultilanguage
     *
     * @param mixed $shopId
     * @param mixed $languageId
     * @param mixed $title
     */
    public function testCountryPerShopAndLanguage(string $shopId, string $languageId, string $title): void
    {
        $this->setGETRequestParameter('shp', $shopId);
        $this->setGETRequestParameter('lang', $languageId);

        $result = $this->query(
            'query{
                country (id: "' . self::ACTIVE_COUNTRY . '") {
                    id
                    title
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertEquals(
            [
                'id'          => self::ACTIVE_COUNTRY,
                'title'       => $title,
            ],
            $result['body']['data']['country']
        );
    }

    public function testCountryListForShop2(): void
    {
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $result = $this->query(
            'query{
                countries {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertCount(
            5,
            $result['body']['data']['countries']
        );
    }
}
