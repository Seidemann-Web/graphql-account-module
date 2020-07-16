<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishList\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasket;
use OxidEsales\GraphQL\Account\WishList\Service\WishList as WishListService;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class WishListTest extends TokenTestCase
{
    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const OTHER_PRODUCT_ID = '_test_product_for_rating_avg';

    private const PRIVATE_WISHLIST_ID = 'test_make_wishlist_private';

    // Public wish list
    private const WISH_LIST_PUBLIC = '_test_wish_list_public';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const USERNAME_ID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    // Private wish list
    private const WISH_LIST_PRIVATE = '_test_wish_list_private';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USERNAME_ID = '245ad3b5380202966df6ff128e9eecaq';

    private const PRODUCT = '_test_product_for_wish_list';

    private const OWNER_ID_WITHOUT_WISH_LIST = 'c0ea0473445326f4b43724e3b76547a5';

    public function testAddInvalidProductToWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->addProductToWishListMutation('not_a_product');

        $this->assertResponseStatus(404, $result);
        $this->assertSame('Product was not found by id: not_a_product', $result['body']['errors'][0]['message']);
    }

    public function testAddProductToWishListNoToken(): void
    {
        $result = $this->query('
            mutation{
                wishListAddProduct(productId: "' . self::PRODUCT_ID . '") {
                    id
                }
            }
        ');

        $this->assertResponseStatus(400, $result);
    }

    public function testMakeWishListPrivateWithToken(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'mutation{
                 wishListMakePrivate {
                    id
                    public
                 }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $this->assertEquals(
            [
                'id'     => self::PRIVATE_WISHLIST_ID,
                'public' => false,
            ],
            $result['body']['data']['wishListMakePrivate']
        );
    }

    public function testGetPublicWishList(): void
    {
        $result = $this->query(
            'query{
                wishList(id: "' . self::WISH_LIST_PUBLIC . '") {
                    items {
                        id
                        amount
                        lastUpdateDate
                        product {
                            id
                        }
                    }
                    id
                    public
                    creationDate
                    lastUpdateDate
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $wishList = $result['body']['data']['wishList'];
        $this->assertEquals(self::WISH_LIST_PUBLIC, $wishList['id']);
        $this->assertEquals(true, $wishList['public']);
        $this->assertNull($wishList['lastUpdateDate']);

        $this->assertCount(1, $wishList['items']);
        $basketItem = $wishList['items'][0];
        $this->assertEquals('_test_wish_list_item_1', $basketItem['id']);
        $this->assertEquals(1, $basketItem['amount']);
        $this->assertEquals(self::PRODUCT, $basketItem['product']['id']);
    }

    public function testGetPrivateWishListAuthorized(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->query(
            'query{
                wishList(id: "' . self::WISH_LIST_PRIVATE . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $wishList = $result['body']['data']['wishList'];

        $this->assertEquals(self::WISH_LIST_PRIVATE, $wishList['id']);
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testGetPrivateWishListNotAuthorized(bool $isLogged): void
    {
        if ($isLogged) {
            $this->prepareToken(self::USERNAME, self::PASSWORD);
        }

        $result = $this->query(
            'query{
                wishList(id: "' . self::WISH_LIST_PRIVATE . '") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(403, $result);
    }

    public function boolDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testGetNotExistingWishList(bool $isLogged): void
    {
        if ($isLogged) {
            $this->prepareToken(self::USERNAME, self::PASSWORD);
        }

        $result = $this->query(
            'query{
                wishList(id: "not-existing-id") {
                    id
                }
            }'
        );

        $this->assertResponseStatus(404, $result);
    }

    public function testMakeWishListPrivateWithoutToken(): void
    {
        $result = $this->query(
            'mutation{
                 wishListMakePrivate {
                    id
                 }
            }'
        );

        $this->assertResponseStatus(400, $result);
    }

    public function testRemoveProductFromWishListWithoutToken(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->addProductToWishListMutation(self::PRODUCT_ID);

        $this->setAuthToken('');
        $result = $this->removeProductFromWishListMutation(self::PRODUCT_ID);
        $this->assertResponseStatus(400, $result);
    }

    public function makeWishListPublicProvider(): array
    {
        return [
            [
                'userHasToken'     => false,
                'expectedStatus'   => 400,
                'expectedWishList' => null,
            ],
            [
                'userHasToken'     => true,
                'expectedStatus'   => 200,
                'expectedWishList' => ['public' => true],
            ],
        ];
    }

    /**
     * @dataProvider makeWishListPublicProvider
     *
     * @param mixed $expectedWishList
     */
    public function testMakeWishListPublic(bool $userHasToken, int $expectedStatus, $expectedWishList): void
    {
        if ($userHasToken) {
            $this->prepareToken(self::USERNAME, self::PASSWORD);
        }

        $result = $this->query('
            mutation {
                wishListMakePublic {
                    public
                }
            }
        ');

        $this->assertResponseStatus($expectedStatus, $result);
        $actualWishList = $result['body']['data']['wishListMakePublic'] ?? null;

        $this->assertEquals($expectedWishList, $actualWishList);
    }

    public function testGetOwnPrivateWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->wishListByOwnerQuery(self::OTHER_USERNAME_ID);
        $this->assertResponseStatus(200, $result);
    }

    public function testGetPublicWishListByOwnerId(): void
    {
        $this->prepareToken();

        $result = $this->wishListByOwnerQuery(self::USERNAME_ID);
        $this->assertResponseStatus(200, $result);
    }

    public function testGetPrivateWishListByOwnerId(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->wishListByOwnerQuery(self::OTHER_USERNAME_ID);
        $this->assertResponseStatus(404, $result);
    }

    public function testGetPublicWishListByOwnerIdWithoutToken(): void
    {
        $result = $this->wishListByOwnerQuery(self::OTHER_USERNAME_ID);
        $this->assertResponseStatus(401, $result);
    }

    public function testGetWishListByNonExistingOwnerId(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->wishListByOwnerQuery('non-existing-owner');
        $this->assertResponseStatus(404, $result);
    }

    public function testGetWishListByOwnerIdWithoutWishList(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->wishListByOwnerQuery(self::OWNER_ID_WITHOUT_WISH_LIST);
        $this->assertResponseStatus(404, $result);
    }

    public function testRemoveProductFromWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->addProductToWishListMutation(self::PRODUCT_ID);

        $result = $this->removeProductFromWishListMutation(self::PRODUCT_ID);
        $this->assertResponseStatus(200, $result);

        $this->assertArrayNotHasKey(
            self::PRODUCT_ID,
            $this->getWishListArticles()
        );
    }

    public function testRemoveMultipleProductsFromWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->addProductToWishListMutation(self::PRODUCT_ID);
        $this->addProductToWishListMutation(self::OTHER_PRODUCT_ID);

        $result = $this->removeProductFromWishListMutation(self::PRODUCT_ID);
        $this->assertResponseStatus(200, $result);

        $products = $this->getWishListArticles();
        $this->assertCount(2, $products);
        $this->assertSame(self::OTHER_PRODUCT_ID, array_pop($products)->getId());
    }

    public function testRemoveProductWhichIsNotPartOfUsersWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->addProductToWishListMutation(self::OTHER_PRODUCT_ID);

        $result = $this->removeProductFromWishListMutation(self::PRODUCT_ID);
        $this->assertResponseStatus(200, $result);
        $products = $this->getWishListArticles();
        $this->assertCount(2, $products);
        $this->assertSame(self::OTHER_PRODUCT_ID, array_pop($products)->getId());
    }

    public function testRemoveNonExistingProductFromWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->addProductToWishListMutation(self::OTHER_PRODUCT_ID);

        $result = $this->removeProductFromWishListMutation('not_a_product');
        $this->assertResponseStatus(404, $result);
        $this->assertSame('Product was not found by id: not_a_product', $result['body']['errors'][0]['message']);
        $products = $this->getWishListArticles();
        $this->assertCount(2, $products);
        $this->assertSame(self::OTHER_PRODUCT_ID, array_pop($products)->getId());
    }

    private function addProductToWishListMutation(string $productId = self::PRODUCT_ID): array
    {
        return $this->query('
            mutation{
                wishListAddProduct(productId: "' . $productId . '") {
                    id
                }
            }
        ');
    }

    private function removeProductFromWishListMutation(string $productId = self::PRODUCT_ID): array
    {
        return $this->query(
            'mutation {
                wishListRemoveProduct(productId: "' . $productId . '") {
                    id
                    public
                    creationDate
                    lastUpdateDate
                }
            }'
        );
    }

    private function getWishList(): EshopUserBasket
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);

        return $user->getBasket(WishListService::SHOP_WISH_LIST_NAME);
    }

    // TODO: When product relation is added to WishList data type use it instead of `getWishListArticles`
    private function getWishListArticles(): array
    {
        return $this->getWishList()->getArticles();
    }

    private function wishListByOwnerQuery(string $ownerId): array
    {
        return $this->query('query {
            wishListByOwnerId(ownerId: "' . $ownerId . '") {
                customer {
                    firstName
                }
                id
                public
                creationDate
                lastUpdateDate
            }
        }');
    }
}
