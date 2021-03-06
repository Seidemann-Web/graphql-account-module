<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\NewsletterStatus\Controller;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\Eshop\Application\Model\user as EshopUser;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class NewsletterStatusTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const OTHER_USER_OXPASSALT = 'b186f117054b700a89de929ce90c6aef';

    private const SUBSCRIPTION_ID = '_othertestuser';

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');

        parent::tearDown();
    }

    public function testNewsletterOptInNoDatabaseEntry(): void
    {
        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '",
            confirmCode:"' . md5(self::OTHER_USERNAME . self::OTHER_USER_OXPASSALT) . '"
          }){
            email
            status
          }
        }');

        $this->assertResponseStatus(404, $result);
        $this->assertEquals(
            'Newsletter subscription status was not found for: ' . self::OTHER_USERNAME,
            $result['body']['errors'][0]['message']
        );
    }

    public function testNewsletterOptInWrongConfirmationCode(): void
    {
        $this->prepareTestData();

        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '",
            confirmCode:"incorrect"
          }){
            email
            status
          }
        }');

        $this->assertResponseStatus(400, $result);
        $this->assertEquals("Wrong e-mail confirmation code 'incorrect'!", $result['body']['errors'][0]['message']);
    }

    public function testNewsletterOptInEmptyEmail(): void
    {
        $this->prepareTestData();

        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"",
            confirmCode:""
          }){
            email
            status
          }
        }');

        $this->assertResponseStatus(400, $result);
        $this->assertEquals('The e-mail address must not be empty!', $result['body']['errors'][0]['message']);
    }

    public function testNewsletterOptInWorks(): void
    {
        $this->prepareTestData();

        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"' . self::OTHER_USERNAME . '",
            confirmCode:"' . md5(self::OTHER_USERNAME . self::OTHER_USER_OXPASSALT) . '"
          }){
            salutation
            firstName
            lastName
            email
            status
            failedEmailCount
            subscribed
            unsubscribed
            updated
          }
        }');

        $this->assertResponseStatus(200, $result);

        $data = $result['body']['data']['newsletterOptIn'];
        $this->assertEquals('SUBSCRIBED', $data['status']);
    }

    public function testNewsletterStatusUnsubscribe(): void
    {
        $this->prepareTestData(1);

        $result = $this->query(
            'mutation {
                newsletterUnsubscribe (newsletterStatus: {
                  email: "' . self::OTHER_USERNAME . '"
                })
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['newsletterUnsubscribe']);

        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->load(self::SUBSCRIPTION_ID);
        $this->assertEquals(0, $subscription->getFieldData('oxdboptin'));

        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);
        $this->assertFalse($user->inGroup('oxidnewsletter'));
    }

    public function testNewsletterStatusUnsubscribeForMissingData(): void
    {
        $result = $this->query(
            'mutation {
                newsletterUnsubscribe (newsletterStatus: {
                  email: "nouser@oxid-esales.com"
                })
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testNewsletterStatusUnsubscribeWithTokenOnly(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                newsletterUnsubscribe
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['newsletterUnsubscribe']);
    }

    public function testNewsletterStatusUnsubscribeForMissingDataOrToken(): void
    {
        $result = $this->query(
            'mutation {
                newsletterUnsubscribe
            }'
        );

        $this->assertResponseStatus(404, $result);
        $this->assertEquals('Missing subscriber email or token', $result['body']['errors']['0']['message']);
    }

    public function testNewsletterStatusUnsubscribePreferInputOverToken(): void
    {
        $this->prepareTestData(1);
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                newsletterUnsubscribe (newsletterStatus: {
                  email: "' . self::OTHER_USERNAME . '"
                })
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertTrue($result['body']['data']['newsletterUnsubscribe']);

        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->load(self::SUBSCRIPTION_ID);
        $this->assertEquals(0, $subscription->getFieldData('oxdboptin'));
    }

    private function prepareTestData(int $optin = 2): void
    {
        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->setId(self::SUBSCRIPTION_ID);
        $subscription->assign(
            [
                'oxuserid'  => self::OTHER_USER_OXID,
                'oxdboptin' => $optin,
                'oxemail'   => self::OTHER_USERNAME,
                'oxfname'   => 'Marc',
                'oxlname'   => 'Muster',
            ]
        );
        $subscription->save();
    }
}
