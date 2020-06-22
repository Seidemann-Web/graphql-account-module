<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\NewsletterStatus\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TestCase;

final class NewsletterStatusTest extends TestCase
{
    public function testNewsletterOptInWorks(): void
    {
        $result = $this->query('mutation{
          newsletterOptIn(newsletterStatus: {
            email:"user@oxid-esales.com",
            confirmCode:"6e700a086090bf0ae085951b0f3150b6"
          }){
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
        }');

        $this->assertResponseStatus(200, $result);

        $data = $result['body']['data']['newsletterOptIn'];
        $this->assertEquals('SUBSCRIBED', $data['status']);
    }
}
