<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Customer\Controller;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class CustomerMultiShopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareTestdata(1, 1);
        $this->prepareTestdata(2, 2);
    }

    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');

        parent::tearDown();
    }

    public function dataProviderCustomerPerShop()
    {
        return [
            'shop_1' => [
                'shopid'   => '1',
                'expected' => 'SUBSCRIBED',
            ],
            'shop_2' => [
                'shopid'   => '2',
                'expected' => 'SUBSCRIBED_MISSING_DBOPTIN',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCustomerPerShop
     */
    public function testUserCustomerPerShopForMallUser(string $shopId, string $expected): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            me {
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame($expected, $result['body']['data']['me']['newsletterStatus']['status']);
    }

    private function prepareTestdata(int $shopid, int $status): void
    {
        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->setId('_othertestuser' . $shopid);
        $subscription->assign(
            [
                'oxuserid'  => self::OTHER_USER_OXID,
                'oxdboptin' => $status,
                'oxshopid'  => $shopid,
            ]
        );
        $subscription->save();
    }
}
