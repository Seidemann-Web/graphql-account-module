<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Codeception\Acceptance;

use OxidEsales\GraphQL\Account\Tests\Codeception\AcceptanceTester;

class GraphQLCest
{
    public function testChangePasswordWithoutTokenFails(AcceptanceTester $I): void
    {
        $I->haveHTTPHeader('Content-Type', 'application/json');
        $I->sendPOST('/widget.php?cl=graphql', [
            'query' => 'mutation {
                userPaswordChange(old: "foobar", new: "foobaz")
            }'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
    }

    /**
     * should be refactored to graphql base module
     */
    private function fetchToken(AcceptanceTester $I): string
    {
        static $token = null;
        if ($token) {
            return $token;
        }
        $token = json_decode(
            file_get_contents('http://oxideshop.local/widget.php?cl=graphql&query=query{token(username:"admin",password:"admin")}')
        )->data->token;
        return $token;
    }

    public function testChangePasswordWithWrongOldPasswordFails(AcceptanceTester $I): void
    {
        $I->haveHTTPHeader('Content-Type', 'application/json');
        $I->amBearerAuthenticated($this->fetchToken($I));
        $I->sendPOST('/widget.php?cl=graphql', [
            'query' => 'mutation {
                userPaswordChange(old: "foobar", new: "foobaz")
            }',
        ]);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
    }

    public function testChangePasswordWithCorrectOldPasswordSucceeds(AcceptanceTester $I): void
    {
        $I->haveHTTPHeader('Content-Type', 'application/json');
        $I->amBearerAuthenticated($this->fetchToken($I));

        $I->sendPOST('/widget.php?cl=graphql', [
            'query' => 'mutation {
                userPaswordChange(old: "admin", new: "foobaz")
            }',
        ]);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->sendPOST('/widget.php?cl=graphql', [
            'query' => 'mutation {
                userPaswordChange(old: "foobaz", new: "admin")
            }',
        ]);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
