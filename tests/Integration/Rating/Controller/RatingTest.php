<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishedPrice\Controller;

use OxidEsales\Eshop\Application\Model\Rating as EshopRatingModel;
use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class RatingTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const USERID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const PRODUCTID = '058c7b525aad619d8b343c0ffada0247';

    private const RATING_DELETE = '_test_rating_delete_';

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxratings', 'oxid');

        parent::tearDown();
    }

    /**
     * @dataProvider ratingUserProvider
     *
     * @param mixed $expectedRatings
     */
    public function testRatingsForUser(array $user, int $status, $expectedRatings): void
    {
        if ($user) {
            $this->prepareToken($user['username'], $user['password']);
        }

        $result = $this->query('query {
            ratings {
                id
            }
        }');

        $this->assertResponseStatus($status, $result);

        $this->assertEquals(
            $expectedRatings,
            $result['body']['data']['ratings']
        );
    }

    public function ratingUserProvider()
    {
        return [
            [
                'user'           => [],
                'expectedStatus' => 403,
                'expectedResult' => null,
            ], [
                'user' => [
                    'username' => 'user@oxid-esales.com',
                    'password' => 'useruser',
                ],
                'expectedStatus' => 200,
                'expectedResult' => [
                    [
                        'id' => '13f810d1aa415400c8abdd37a5b2181a',
                    ],
                    [
                        'id' => '944374b68a8b26d8d95a8b11ad574a75',
                    ],
                    [
                        'id' => 'bcb64c798fd5ec58e5a6de30d52afee2',
                    ],
                    [
                        'id' => 'c62d0873a0ed83aeed879c83aa863f23',
                    ],
                    [
                        'id' => 'e7aa4c3a8508491a7e875f26b51fe4d0',
                    ],
                    [
                        'id' => 'test_rating_1_',
                    ],
                ],
            ], [
                'user' => [
                    'username' => 'otheruser@oxid-esales.com',
                    'password' => 'useruser',
                ],
                'expectedStatus' => 200,
                'expectedResult' => [
                    [
                        'id' => 'test_user_rating',
                    ],
                ],
            ],
        ];
    }

    public function testSetRatingWithoutToken(): void
    {
        $result = $this->query(
            'mutation {
                ratingSet(ratingInput: {
                    rating: 5,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testSetRating(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingSet(rating: {
                    rating: 5,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                    product{
                        id
                    }
                    rating
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $ratingData = $result['body']['data']['ratingSet'];

        $id = $ratingData['id'];
        $this->assertStringMatchesFormat('%s', $id);
        $this->assertSame(self::PRODUCTID, $ratingData['product']['id']);
        $this->assertSame(5, $ratingData['rating']);

        $result = $this->query(
            'query {
                rating(id: "' . $id . '") {
                    id
                    rating
                }
            }'
        );

        $this->assertSame(5, $result['body']['data']['rating']['rating']);
    }

    public function testSetRatingOutOfBounds(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingSet(rating: {
                    rating: 6,
                    productId: "' . self::PRODUCTID . '"
                }){
                    id
                    product{
                        id
                    }
                    rating
                }
            }'
        );

        $this->assertResponseStatus(400, $result);
        $this->assertSame('Rating must be between 1 and 5, was 6', $result['body']['errors'][0]['debugMessage']);
    }

    public function testSetRatingWrongProduct(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation {
                ratingSet(rating: {
                    rating: 5,
                    productId: "some_not_existing_product"
                }){
                    id
                    product{
                        id
                    }
                    rating
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
        $this->assertSame(
            'Product was not found by id: some_not_existing_product',
            $result['body']['errors'][0]['message']
        );
    }

    public function providerDeleteRating()
    {
        return [
            'admin' => [
                'username' => 'admin',
                'password' => 'admin',
                'expected' => 200,
            ],
            'user'  => [
                'username' => 'user@oxid-esales.com',
                'password' => 'useruser',
                'expected' => 200,
            ],
            'otheruser'  => [
                'username' => 'otheruser@oxid-esales.com',
                'password' => 'useruser',
                'expected' => 401,
            ],
        ];
    }

    /**
     * @dataProvider providerDeleteRating
     */
    public function testDeleteRating(string $username, string $password, int $expected): void
    {
        $rating = oxNew(EshopRatingModel::class);
        $rating->assign(
            [
                'oxid'       => self::RATING_DELETE,
                'oxshopid'   => '1',
                'oxuserid'   => self::USERID,
                'oxtype'     => 'oxarticle',
                'oxobjectid' => 'b56597806428de2f58b1c6c7d3e0e093',
                'oxrating'   => 3,
            ]
        );
        $rating->save();

        $this->prepareToken($username, $password);

        $result = $this->query(
            'mutation {
                ratingDelete(id: "' . self::RATING_DELETE . '"){
                    id
                }
            }'
        );

        $this->assertResponseStatus($expected, $result);
    }
}
