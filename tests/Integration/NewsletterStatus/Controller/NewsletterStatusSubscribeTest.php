<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\NewsletterStatus\Controller;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\Eshop\Core\Email as EshopEmail;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\Eshop\Core\UtilsObject as EshopUtilsObject;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class NewsletterStatusSubscribeTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const SUBSCRIPTION_ID = '_othertestuser';

    private const OTHER_USER_PASSWORD = 'useruser';

    protected function setUp(): void
    {
        parent::setUp();

        EshopRegistry::getConfig()->setConfigParam('blOrderOptInEmail', true);
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');
        EshopUtilsObject::setClassInstance(EshopEmail::class, null);

        parent::tearDown();
    }

    public function testNewsletterSubscribeMissingInputData(): void
    {
        $result = $this->query(
            'mutation {
                newsletterSubscribe (newsletterStatus: {})
                {
                   status
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testNewsletterSubscribeMissingInputDataButToken(): void
    {
        $this->setMailMock();
        $this->prepareTestData(0);
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_USER_PASSWORD);

        $result = $this->query(
            'mutation {
                newsletterSubscribe (newsletterStatus: {})
                {
                   status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED_MISSING_DBOPTIN', $result['body']['data']['newsletterSubscribe']['status']);
        $this->assertSubscriptionStatus('SUBSCRIBED_MISSING_DBOPTIN');
    }

    public function testNewsletterSubscribeExistingUserWithoutToken(): void
    {
        $this->setMailMock();
        $this->prepareTestData(0);

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  email: "' . self::OTHER_USERNAME . '"
                }) {
                    status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED_MISSING_DBOPTIN', $result['body']['data']['newsletterSubscribe']['status']);
        $this->assertSubscriptionStatus('SUBSCRIBED_MISSING_DBOPTIN');
    }

    public function testNewsletterSubscribeExistingSubcribedUser(): void
    {
        $this->setMailMock();
        $this->prepareTestData(1);

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  email: "' . self::OTHER_USERNAME . '"
                }) {
                    status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED_MISSING_DBOPTIN', $result['body']['data']['newsletterSubscribe']['status']);
        $this->assertSubscriptionStatus('SUBSCRIBED_MISSING_DBOPTIN');
    }

    public function testNewsletterSubscribeExistingSubcribedUserByToken(): void
    {
        $this->setMailMock('never');
        $this->prepareTestData(1);
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_USER_PASSWORD);

        $result = $this->query(
            'mutation {
                newsletterSubscribe (newsletterStatus: {})
                {
                    status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);
        $this->assertEquals('SUBSCRIBED', $result['body']['data']['newsletterSubscribe']['status']);
        $this->assertSubscriptionStatus('SUBSCRIBED');
    }

    public function providerNewsletterSubscribeNotExistingUser()
    {
        $newUserMail = rand(0, 10) . mktime() . '@oxid-esales.com';

        return [
            'max_data' => [
                'input' => [
                    'salutation' => 'mrs',
                    'firstname'  => 'Newgirl',
                    'lastname'   => 'Intown',
                    'email'      => $newUserMail,
                    'status'     => 'SUBSCRIBED_MISSING_DBOPTIN',
                ],
                'require_optin' => true,
                'mock'          => 'once',
            ],
            'min_data' => [
                'input' => [
                    'salutation' => '',
                    'firstname'  => '',
                    'lastname'   => '',
                    'email'      => '2' . $newUserMail,
                    'status'     => 'SUBSCRIBED_MISSING_DBOPTIN',
                ],
                'require_optin' => true,
                'mock'          => 'once',
            ],
            'no_optin_required' => [
                'input' => [
                    'salutation' => '',
                    'firstname'  => '',
                    'lastname'   => '',
                    'email'      => '3' . $newUserMail,
                    'status'     => 'SUBSCRIBED',
                ],
                'require_optin' => false,
                'mock'          => 'never',
            ],
        ];
    }

    /**
     * @dataProvider providerNewsletterSubscribeNotExistingUser
     *
     * @param mixed $input
     */
    public function testNewsletterSubscribeNotExistingUser(array $input, bool $optin, string $mock): void
    {
        EshopRegistry::getConfig()->setConfigParam('blOrderOptInEmail', $optin);
        $this->setMailMock($mock);
        $newUserMail = $input['email'];

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  salutation: "' . $input['salutation'] . '"
                  firstName: "' . $input['firstname'] . '"
                  lastName: "' . $input['lastname'] . '"
                  email: "' . $newUserMail . '"
                }) {
                    salutation
                    firstname
                    lastname
                    email
                    status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertEquals($input, $result['body']['data']['newsletterSubscribe']);
    }

    public function dataProviderNewsletterSubscribeNotExistingUserIncompleteInput()
    {
        return [
            'empty_email' => [
                'data' => [
                    'salutation' => 'mrs',
                    'firstName'  => 'NewGirl',
                    'lastName'   => 'InTown',
                    'email'      => '',
                ],
                'expected' => 'Email is not valid',
            ],
            'invalid_email' => [
                'data' => [
                    'salutation' => 'mrs',
                    'firstName'  => 'NewGirl',
                    'lastName'   => 'InTown',
                    'email'      => 'admin',
                ],
                'expected' => 'Email is not valid',
            ],
            'crazy_input' => [
                'data' => [
                    'salutation' => 'mrs',
                    'firstName'  => 'NewGirl',
                    'lastName'   => 'InTown',
                    'email'      => str_pad('x', 1000) . '@oxid-esales.com',
                ],
                'expected' => 'Email is not valid',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderNewsletterSubscribeNotExistingUserIncompleteInput
     *
     * @param mixed $data
     * @param mixed $expected
     */
    public function testNewsletterSubscribeInvalidInput($data, $expected): void
    {
        $template = 'mutation {
                newsletterSubscribe(newsletterStatus: {
                  salutation: "%s"
                  firstName: "%s"
                  lastName: "%s"
                  email: "%s"
                }) {
                    status
                }
            }';

        $result = $this->query(sprintf($template, ...array_values($data)));

        $this->assertResponseStatus(400, $result);
        $this->assertEquals($expected, $result['body']['errors'][0]['debugMessage']);
    }

    public function testNewsletterSubscribeExistingUserDifferentInputGetsIgnored(): void
    {
        $this->setMailMock();
        $this->prepareTestData(0);

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  salutation: "mrs"
                  firstName: "Newgirl"
                  lastName: "Intown"
                  email: "' . self::OTHER_USERNAME . '"
                }) {
                    salutation
                    firstname
                    lastname
                    email
                    status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $expected = [
            'salutation' => '',
            'firstname'  => 'Marc',
            'lastname'   => 'Muster',
            'email'      => self::OTHER_USERNAME,
            'status'     => 'SUBSCRIBED_MISSING_DBOPTIN',
        ];
        $this->assertEquals($expected, $result['body']['data']['newsletterSubscribe']);
        $this->assertSubscriptionStatus('SUBSCRIBED_MISSING_DBOPTIN');
    }

    public function testNewsletterSubscribePreferInputOverToken(): void
    {
        $this->setMailMock();
        $this->prepareTestData(0);
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                newsletterSubscribe(newsletterStatus: {
                  salutation: "mrs"
                  firstName: "Newgirl"
                  lastName: "Intown"
                  email: "' . self::OTHER_USERNAME . '"
                }) {
                    salutation
                    firstname
                    lastname
                    email
                    status
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $expected = [
            'salutation' => '',
            'firstname'  => 'Marc',
            'lastname'   => 'Muster',
            'email'      => self::OTHER_USERNAME,
            'status'     => 'SUBSCRIBED_MISSING_DBOPTIN',
        ];
        $this->assertEquals($expected, $result['body']['data']['newsletterSubscribe']);
        $this->assertSubscriptionStatus('SUBSCRIBED_MISSING_DBOPTIN');
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

    private function assertSubscriptionStatus(string $status, string $email = self::OTHER_USERNAME): void
    {
        $this->prepareToken($email, self::PASSWORD);

        $result = $this->query('query {
            me {
                id
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertEquals($status, $result['body']['data']['me']['newsletterStatus']['status']);
    }

    /**
     * Test helper to prevent system from actually sending mails.
     *
     * For unit tests (OXID_PHP_UNIT defined), class instances are taken
     * from \OxidEsales\Eshop\Core\UtilsObjec class instance cache if they exist.
     * To ensure mail sending is suppressed, we need to ensure that the correct mail class
     * is mocked. Shop mail class is chain extended by B2B in our case here.
     * Use oxNew to ensure the class chain for Mail class is built, then use get_class to
     * get the actual mail class name (the top most in class chain), then create a mock and
     * store that mock in class instance cache.
     */
    private function setMailMock(string $called = 'once'): void
    {
        $actualMailClass = get_class(oxNew(EshopEmail::class));
        $mailMock        = $this->getMockBuilder($actualMailClass)
            ->setMethods(['sendNewsletterDbOptInMail'])
            ->getMock();

        $mailMock->expects($this->$called())
            ->method('sendNewsletterDbOptInMail')
            ->will($this->returnValue(true));
        EshopUtilsObject::setClassInstance(EshopEmail::class, $mailMock);
    }
}
