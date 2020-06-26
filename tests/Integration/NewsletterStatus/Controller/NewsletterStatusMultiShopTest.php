<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\NewsletterStatus\Controller;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class NewsletterStatusMultiShopTest extends MultishopTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const OTHER_USER_OXPASSALT = 'b186f117054b700a89de929ce90c6aef';

    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');

        parent::tearDown();
    }

    public function dataProviderNewsletterStatusPerShop()
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
     * @dataProvider dataProviderNewsletterStatusPerShop
     */
    public function testUserNewsletterStatusOptinPerShop(string $shopId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareTestdata((int) $shopId);
        $this->assignUserToShop((int) $shopId);

        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '",
            confirmCode:"' . md5(self::OTHER_USERNAME . self::OTHER_USER_OXPASSALT) . '"
          }){
            email
            status
          }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('SUBSCRIBED', $result['body']['data']['newsletterOptIn']['status']);
    }

    public function dataProviderNewsletterStatusMallUser()
    {
        return [
            'malluser' => [
                'flag'     => true,
                'expected' => 200,
                'verify'   => true,
            ],
            'no_malluser' => [
                'flag'     => false,
                'expected' => 404,
                'verify'   => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderNewsletterStatusMallUser
     */
    public function testNewsletterOptInForMallUserFromOtherSubshop(bool $flag, int $expected, bool $verify): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', $flag);

        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareTestdata(2);
        $this->assignUserToShop(1);

        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '",
            confirmCode:"' . md5(self::OTHER_USERNAME . self::OTHER_USER_OXPASSALT) . '"
          }){
            email
            status
          }
        }');

        $this->assertResponseStatus($expected, $result);

        if ($verify) {
            $this->assertSame('SUBSCRIBED', $result['body']['data']['newsletterOptIn']['status']);
        }
    }

    /**
     * @dataProvider dataProviderNewsletterStatusPerShop
     */
    public function testNewsletterUnsubscribePerShop(string $shopId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareTestdata((int) $shopId);
        $this->assignUserToShop((int) $shopId);

        $result = $this->query('mutation{
          newsletterUnsubscribe(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '"
          }){
            email
          }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame(self::OTHER_USERNAME, $result['body']['data']['newsletterUnsubscribe']['email']);
    }

    /**
     * @dataProvider dataProviderNewsletterStatusMallUser
     */
    public function testNewsletterUbsubcribeForMallUserFromOtherSubshop(bool $flag, int $expected, bool $verify): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', $flag);

        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareTestdata(2);
        $this->assignUserToShop(1);

        $result = $this->query('mutation{
          newsletterUnsubscribe(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '"
          }){
            email
          }
        }');

        $this->assertResponseStatus($expected, $result);

        if ($verify) {
            $this->assertSame(self::OTHER_USERNAME, $result['body']['data']['newsletterUnsubscribe']['email']);
        }
    }

    private function prepareTestdata(int $shopid): void
    {
        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->setId('_othertestuser' . $shopid);
        $subscription->assign(
            [
                'oxuserid'  => self::OTHER_USER_OXID,
                'oxemail'   => self::OTHER_USERNAME,
                'oxdboptin' => 2,
                'oxshopid'  => $shopid,
            ]
        );
        $subscription->save();
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
