<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class BasketsMultishopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    /**
     * @dataProvider shopDataProvider
     */
    public function testGetBasketsPerShops(int $shopId, int $basketsCount): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', (string) $shopId);

        $response = $this->basketsQuery(self::USERNAME);
        $this->assertResponseStatus(200, $response);

        $baskets = $response['body']['data']['baskets'];
        $this->assertCount($basketsCount, $baskets);
    }

    /**
     * @dataProvider shopDataProvider
     */
    public function testGetBasketsPerShopsWithMallUser(int $shopId): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', (string) $shopId);

        $response = $this->basketsQuery(self::USERNAME);
        $this->assertResponseStatus(200, $response);

        $baskets = $response['body']['data']['baskets'];
        $this->assertCount(4, $baskets);
    }

    public function shopDataProvider(): array
    {
        return [
            [1, 3],
            [2, 1],
        ];
    }

    private function basketsQuery(string $owner): array
    {
        return $this->query('query {
            baskets(owner: "' . $owner . '") {
                owner {
                    lastName
                }
                items(pagination: {limit: 10, offset: 0}) {
                    product {
                        title
                    }
                    amount
                }
                id
                title
                public
                creationDate
                lastUpdateDate
            }
        }');
    }
}
