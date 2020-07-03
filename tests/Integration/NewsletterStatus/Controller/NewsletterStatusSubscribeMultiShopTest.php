<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\NewsletterStatus\Controller;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class NewsletterStatusSubscribeMultiShopTest extends MultishopTestCase
{
    private const DIFFERENT_USERNAME = 'differentuser@oxid-esales.com';

    private const DIFFERENT_USER_OXID = '_45ad3b5380202966df6ff128e9eecaq';

    private const DIFFERENT_USER_PASSWORD = 'useruser';

    protected function setUp(): void
    {
        parent::setUp();

        $shop = oxNew(Shop::class);
        $shop->load(2);
        $shop->assign(
            [
                'oxorderemail' => 'reply@myoxideshop.com',
                'oxinfoemail'  => 'info@myoxideshop.com',
            ]
        );
        $shop->save();

        EshopRegistry::getConfig()->setConfigParam('blOrderOptInEmail', true);
    }

    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');
        $this->cleanUpTable('oxnewssubscribed', 'oxuserid');

        parent::tearDown();
    }

    public function dataProviderNewsletterSubscribePerShop()
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
     * @dataProvider dataProviderNewsletterSubscribePerShop
     */
    public function testUserNewsletterSubscribePerShopWithoutToken(string $shopId): void
    {
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        $this->prepareTestdata((int) $shopId, 0);
        $this->assignUserToShop((int) $shopId);

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  email: "' . self::DIFFERENT_USERNAME . '"
                }) {
                  status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED_MISSING_DBOPTIN', $result['body']['data']['newsletterSubscribe']['status']);
        $this->assertSubscriptionStatus(2, (int) $shopId);
    }

    public function testNewsletterStatusMallUserSubscribeFromToken(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);

        $this->prepareTestdata(1, 0);
        $this->assignUserToShop(1);

        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');
        $this->prepareToken(self::DIFFERENT_USERNAME, self::DIFFERENT_USER_PASSWORD);

        $result = $this->query('mutation{
            newsletterSubscribe (newsletterStatus: {})
            {
                status
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED_MISSING_DBOPTIN', $result['body']['data']['newsletterSubscribe']['status']);

        //malluser is still not subscribed in shop 1 but subscribed in shop 2 (before optin)
        $this->assertSubscriptionStatus(0, 1);
        $this->assertSubscriptionStatus(2, 2);
    }

    public function dataProviderNewsletterStatusMallUser()
    {
        return [
            'malluser' => [
                'flag'     => true,
                'sameuser' => true,
            ],
            'no_malluser' => [
                'flag'     => false,
                'sameuser' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderNewsletterStatusMallUser
     */
    public function testNewsletterSubscribeForMallUserFromOtherSubshop(bool $flag, bool $expectSameUser): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', $flag);
        $this->prepareTestdata(1, 0);
        $this->assignUserToShop(1);

        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  salutation: "mrs"
                  firstName: "Newgirl"
                  lastName: "Intown"
                  email: "' . self::DIFFERENT_USERNAME . '"
                }){
                  status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED_MISSING_DBOPTIN', $result['body']['data']['newsletterSubscribe']['status']);

        $this->assertSubscriptionStatus(0, 1);
        $userId = $this->assertSubscriptionStatus(2, 2);

        if ($expectSameUser) {
            $this->assertEquals(self::DIFFERENT_USER_OXID, $userId);
        } else {
            $this->assertNotEquals(self::DIFFERENT_USER_OXID, $userId);
        }
    }

    private function prepareTestdata(int $shopid, int $optin = 2): void
    {
        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->setId('_othertestuser' . $shopid);
        $subscription->assign(
            [
                'oxuserid'  => self::DIFFERENT_USER_OXID,
                'oxemail'   => self::DIFFERENT_USERNAME,
                'oxdboptin' => $optin,
                'oxshopid'  => $shopid,
            ]
        );
        $subscription->save();
    }

    private function assignUserToShop(int $shopid): void
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::DIFFERENT_USER_OXID);
        $user->assign(
            [
                'oxshopid' => $shopid,
            ]
        );
        $user->save();
    }

    private function assertSubscriptionStatus(
        int $expected,
        int $shopid,
        string $email = self::DIFFERENT_USERNAME
    ): string {
        EshopRegistry::getConfig()->setShopId($shopid);

        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->loadFromEmail($email);
        $this->assertEquals($shopid, $subscription->getFieldData('oxshopid'));
        $this->assertEquals($expected, $subscription->getFieldData('oxdboptin'));

        return $subscription->getFieldData('oxuserid');
    }
}
