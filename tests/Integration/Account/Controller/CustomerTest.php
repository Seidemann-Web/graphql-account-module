<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsSubscribed;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class CustomerTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

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

    public function testMeForNotLoggedInUser(): void
    {
        $result = $this->query('query {
            me {
               id
               firstName
            }
        }');

        $this->assertResponseStatus(400, $result);
    }

    public function testMeNewsletterStatusNoEntryInDatabase(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query('query {
            me {
                id
                firstName
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('Marc', $result['body']['data']['me']['firstName']);
        $this->assertNull($result['body']['data']['me']['newsletterStatus']);
    }

    public function testMeNewsletterStatusInvalidEntryInDatabase(): void
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
            me {
                id
                firstName
                newsletterStatus {
                    status
                }
            }
        }');

        $this->assertResponseStatus(200, $result);
        $this->assertSame('UNSUBSCRIBED', $result['body']['data']['me']['newsletterStatus']['status']);
    }

    public function testMeAndNewsletterStatusForUser(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('query {
            me {
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

        $this->assertContains('T', $result['body']['data']['me']['newsletterStatus']['updated']);
        unset($result['body']['data']['me']['newsletterStatus']['updated']);

        $this->assertEquals(
            $expected,
            $result['body']['data']['me']['newsletterStatus']
        );
    }
}
