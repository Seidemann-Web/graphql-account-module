<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Newsletter\Controller;

use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class NewsletterTest extends TokenTestCase
{

    public function newsletterUserProvider()
    {
        return [
            [
                'user'           => [],
                'expectedStatus' => 403,
                'expectedResult' => null,
            ],
            [
                'user' => [
                    'username' => 'admin',
                    'password' => 'admin',
                ],
                'expectedStatus' => 200,
                'expectedResult' => [
                    [
                        'salutation' => 'MR',
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                        'email' => 'admin',
                        'status' => 'subscribed',
                        'failedEmailCount' => 0,
                        'subscribed' => '2005-07-26T19:16:09+02:00',
                        'unsubscribed' => '-0001-11-30T00:00:00+00:53',
                        'created' => '2020-06-23T15:46:25+02:00'

                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider newsletterUserProvider
     *
     * @param mixed $expectedNewsletters
     */
    public function testNewslettersForUser(array $user, int $status, $expectedNewsletters): void
    {
        if ($user) {
            $this->prepareToken($user['username'], $user['password']);
        }

        $result = $this->query('query {
            newsletters {
                salutation
                firstname
                lastname
                email
                status
                failedEmailCount
                subscribed
                unsubscribed
                created
            }
        }');

        $this->assertResponseStatus($status, $result);

        $this->assertEquals(
            $expectedNewsletters,
            $result['body']['data']['newsletters']
        );
    }
}
