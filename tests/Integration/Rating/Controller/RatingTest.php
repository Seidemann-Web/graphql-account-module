<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Rating\Controller;

use OxidEsales\Eshop\Application\Model\Rating as EshopRatingModel;
use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class RatingTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const USERID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    private const PRODUCTID = '058c7b525aad619d8b343c0ffada0247';

    private const RATING_DELETE = '_test_rating_delete_';

    private const SET_RATING_PRODUCTID = '_test_product_for_rating_5_';

    private const SET_ONLY_ONE_RATING_PRODUCTID = '_test_product_for_rating_6_';

    private const PRODUCT_RELATION = '05833e961f65616e55a2208c2ed7c6b8';

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
                    productId: "' . self::SET_RATING_PRODUCTID . '"
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
        $this->assertSame(self::SET_RATING_PRODUCTID, $ratingData['product']['id']);
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

    public function testSetRatingOnlyOnePerProduct(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $query =  'mutation {
                ratingSet(rating: {
                    rating: %d,
                    productId: "' . self::SET_ONLY_ONE_RATING_PRODUCTID . '"
                }){
                    id
                    product{
                        id
                    }
                    rating
                }
            }';

        $result = $this->query(sprintf($query, 5));

        $this->assertResponseStatus(200, $result);
        $ratingId = $result['body']['data']['ratingSet']['id'];

        //try to rate again
        $result = $this->query(sprintf($query, 4));
        $this->assertResponseStatus(404, $result);
        $this->assertSame(
            "You already rated product '_test_product_for_rating_6_', please delete existing rating first.",
            $result['body']['errors'][0]['message']
        );

        //delete
        $result = $this->query(
            'mutation {
                ratingDelete(id: "' . $ratingId . '")
            }'
        );
        $this->assertResponseStatus(200, $result);

        //rate again
        $result = $this->query(sprintf($query, 4));
        $this->assertResponseStatus(200, $result);
        $newRatingId = $result['body']['data']['ratingSet']['id'];

        $this->assertNotEmpty($newRatingId);
        $this->assertNotEquals($ratingId, $newRatingId);
    }

    public function testRelationBetweenRatingAndProductRating(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $query =  'mutation {
                ratingSet(rating: {
                    rating: %d,
                    productId: "' . self::PRODUCT_RELATION . '"
                }){
                    id
                    product {
                        id
                        rating {
                            rating
                            count
                        }
                    }
                    rating
                }
            }';

        $ratingValue = 5;
        $result      = $this->query(sprintf($query, $ratingValue));

        $this->assertResponseStatus(200, $result);
        $rating        = $result['body']['data']['ratingSet']['rating'];
        $productRating = $result['body']['data']['ratingSet']['product']['rating'];
        $this->assertEquals($ratingValue, $productRating['rating']);
        $this->assertSame($ratingValue, $rating);
        $this->assertSame(1, $productRating['count']);

//        //delete
//        $ratingId = $result['body']['data']['ratingSet']['id'];
//        $result = $this->query(
//            'mutation {
//                ratingDelete(id: "' . $ratingId . '")
//            }'
//        );
//        $this->assertResponseStatus(200, $result);
//
//        //rate again
//        $newRatingValue = 4;
//        $result = $this->query(sprintf($query, $newRatingValue));
//        $this->assertResponseStatus(200, $result);
//        $newRating = $result['body']['data']['ratingSet']['rating'];
//        $newProductRating = $result['body']['data']['ratingSet']['product']['rating'];
//        $this->assertSame($newRatingValue, $newProductRating['rating']);
//        $this->assertSame($newRatingValue, $newRating);
//        $this->assertSame(1, $newProductRating['count']);
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
                ratingDelete(id: "' . self::RATING_DELETE . '")
            }'
        );

        $this->assertResponseStatus($expected, $result);
    }
}
