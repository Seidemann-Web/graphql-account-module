<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\NewsletterStatus\Controller;

use OxidEsales\Eshop\Application\Model\Newsletter as EshopNewsletterModel;
use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class NewsletterTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const USERID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    public function testNewsletterUnsubscribeWithToken(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                newsletterUnsubscribe (newsletterStatus: {
                  email: "' . self::USERNAME . '"
                }) {
                  email
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertSame(self::USERNAME, $result['body']['data']['newsletterUnsubscribe']['email']);
    }
}
