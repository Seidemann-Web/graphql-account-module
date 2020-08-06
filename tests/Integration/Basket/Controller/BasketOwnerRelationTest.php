<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketOwnerRelationTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const USER_ID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    public function testGetPublicBasketWhichOwnerDoesNotExist(): void
    {
        $basketId = $this->createPublicBasket();
        $this->deleteUser(self::USER_ID);

        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);
        $result = $this->query(
            'query {
                basket(id: "' . $basketId . '") {
                    id
                    owner {
                        firstName
                    }
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    private function createPublicBasket(): string
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query('mutation {
            basketCreate(basket: {title: "new-basket-list", public: true}) {
                id
            }
        }');

        $this->setAuthToken('');

        return $result['body']['data']['basketCreate']['id'];
    }

    private function deleteUser(string $userId): void
    {
        $db = self::getDb();
        $db->execute('delete from oxuser where oxid = :oxid', ['oxid' => $userId]);
    }
}
