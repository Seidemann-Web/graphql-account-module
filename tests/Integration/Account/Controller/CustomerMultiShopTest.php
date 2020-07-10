<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Customer\Controller;

use Datetime;
use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class CustomerMultiShopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const PRIMARY_SHOP_USERNAME = 'user@oxid-esales.com';

    private const PRIMARY_SHOP_PASSWORD = 'useruser';

    private const PRIMARY_SHOP_USER_OXID = '123ad3b5380202966df6ff128e9eecaq';

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

    public function dataProviderCustomerNewsletterPerShop()
    {
        return [
            'shop_1' => [
                'shopid'   => '1',
                'expected' => 'SUBSCRIBED',
            ],
            'shop_2' => [
                'shopid'   => '2',
                'expected' => 'MISSING_DOUBLE_OPTIN',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCustomerNewsletterPerShop
     */
    public function testCustomerNewsletterPerShopForMallUser(string $shopId, string $expected): void
    {
        $this->ensureShop((int) $shopId);
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);

        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            customer {
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame($expected, $result['body']['data']['customer']['newsletterStatus']['status']);
    }

    public function testCustomerExistingInBothShopsLoggedIntoSecondaryShop(): void
    {
        $this->ensureShop(2);
        EshopRegistry::getConfig()->setShopId('2');
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::PRIMARY_SHOP_USERNAME, self::PRIMARY_SHOP_PASSWORD);

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

        $this->assertEquals(self::PRIMARY_SHOP_USER_OXID, $customerData['id']);
        $this->assertEquals('Marc', $customerData['firstName']);
        $this->assertEquals('Muster', $customerData['lastName']);
        $this->assertEquals(self::PRIMARY_SHOP_USERNAME, $customerData['email']);
        $this->assertEquals('8', $customerData['customerNumber']);
        $this->assertSame(0, $customerData['points']);
        $this->assertSame('1984-12-22T00:00:00+01:00', $customerData['birthdate']);
        $this->assertSame('2011-02-01T08:41:25+01:00', $customerData['registered']);
        $this->assertSame('2011-02-01T08:41:25+01:00', $customerData['created']);
        $this->assertInstanceOf(DateTime::class, DateTime::createFromFormat(DateTime::ATOM, $customerData['updated']));
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
