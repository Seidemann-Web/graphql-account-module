<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Basket\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class BasketsTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_ID = 'test_make_wishlist_private'; //owned by user@oxid-esales.com

    private const BASKET_ID_2 = '_test_basket_private'; //owned by otheruser@oxid-esales.com

    private const BASKET_ID_3 = '_test_wish_list_private'; //owned by otheruser@oxid-esales.com

    private const LAST_NAME = 'Muster';

    public function testBasketsWithoutToken(): void
    {
        $response = $this->basketsQuery(self::USERNAME);
        $this->assertResponseStatus(200, $response);

        $baskets = $response['body']['data']['baskets'];
        $this->assertCount(3, $baskets);
    }

    public function testGetOnlyPublicBaskets(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);
        $this->basketMakePrivateMutation(self::BASKET_ID);

        $response = $this->basketsQuery(self::USERNAME);
        $this->assertResponseStatus(200, $response);

        $baskets = $response['body']['data']['baskets'];
        $this->assertCount(2, $baskets);

        // restore database
        $this->basketMakePublicMutation(self::BASKET_ID);
    }

    public function testGetBasketsFromOtherUser(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);
        $this->basketMakePublicMutation(self::BASKET_ID_2);

        $this->prepareToken(self::USERNAME, self::PASSWORD);
        $response = $this->basketsQuery(self::OTHER_USERNAME);
        $this->assertResponseStatus(200, $response);

        $baskets = $response['body']['data']['baskets'];
        $this->assertCount(1, $baskets);

        // restore database
        $this->basketMakePrivateMutation(self::BASKET_ID_2);
    }

    public function testGetBasketsByLastName(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);
        $this->basketMakePublicMutation(self::BASKET_ID_2);
        $this->basketMakePublicMutation(self::BASKET_ID_3);

        $response = $this->basketsQuery(self::LAST_NAME);
        $this->assertResponseStatus(200, $response);

        $baskets = $response['body']['data']['baskets'];
        $this->assertCount(5, $baskets);
    }

    private function basketsQuery(string $owner): array
    {
        return $this->query('query {
            baskets(owner: "' . $owner . '") {
                owner {
                    lastName
                }
                items(pagination: {limit: 10, offset: 0}) {
                    product {
                        title
                    }
                    amount
                }
                id
                title
                public
                creationDate
                lastUpdateDate
            }
        }');
    }

    private function basketMakePrivateMutation(string $basketId): array
    {
        return $this->query('mutation {
            basketMakePrivate(id: "' . $basketId . '") {
                public
            }
        }');
    }

    private function basketMakePublicMutation(string $basketId): array
    {
        return $this->query('mutation {
            basketMakePublic(id: "' . $basketId . '") {
                public
            }
        }');
    }
}
