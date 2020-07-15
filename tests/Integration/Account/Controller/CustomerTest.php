<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use DateTime;
use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class CustomerTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const USER_OXID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxnewssubscribed', 'oxid');

        parent::tearDown();
    }

    public function testCustomerForNotLoggedInUser(): void
    {
        $result = $this->query('query {
            customer {
               id
               firstName
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testCustomerForLoggedInUser(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

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

        $this->assertEquals(self::USER_OXID, $customerData['id']);
        $this->assertEquals('Marc', $customerData['firstName']);
        $this->assertEquals('Muster', $customerData['lastName']);
        $this->assertEquals(self::USERNAME, $customerData['email']);
        $this->assertEquals('2', $customerData['customerNumber']);
        $this->assertSame(0, $customerData['points']);
        $this->assertSame('1984-12-21T00:00:00+01:00', $customerData['birthdate']);
        $this->assertSame('2011-02-01T08:41:25+01:00', $customerData['registered']);
        $this->assertSame('2011-02-01T08:41:25+01:00', $customerData['created']);
        $this->assertInstanceOf(DateTime::class, DateTime::createFromFormat(DateTime::ATOM, $customerData['updated']));
    }

    public function testCustomerNewsletterStatusNoEntryInDatabase(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            customer {
                id
                firstName
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('Marc', $result['body']['data']['customer']['firstName']);
        $this->assertNull($result['body']['data']['customer']['newsletterStatus']);
    }

    public function testCustomerNewsletterStatusInvalidEntryInDatabase(): void
    {
        $subscription = oxNew(EshopNewsSubscribed::class);
        $subscription->setId('_othertestuser');
        $subscription->assign(
            [
                'oxuserid'  => self::OTHER_USER_OXID,
                'oxdboptin' => 6,
            ]
        );
        $subscription->save();

        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            customer {
                id
                firstName
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('UNSUBSCRIBED', $result['body']['data']['customer']['newsletterStatus']['status']);
    }

    public function testCustomerAndNewsletterStatusForUser(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            customer {
                id
                firstName
                newsletterStatus {
                    salutation
                    firstname
                    lastname
                    email
                    status
                    failedEmailCount
                    subscribed
                    unsubscribed
                    updated
                }
            }
        }');

        $this->assertResponseStatus(200, $result);

        $expected = [
            'salutation'       => 'MR',
            'firstname'        => 'Marc',
            'lastname'         => 'Muster',
            'email'            => self::USERNAME,
            'status'           => 'SUBSCRIBED',
            'failedEmailCount' => 0,
            'subscribed'       => '2020-04-01T11:11:11+02:00',
            'unsubscribed'     => null,
        ];

        $this->assertContains('T', $result['body']['data']['customer']['newsletterStatus']['updated']);
        unset($result['body']['data']['customer']['newsletterStatus']['updated']);

        $this->assertEquals(
            $expected,
            $result['body']['data']['customer']['newsletterStatus']
        );
    }
}
