<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishList\Controller;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasket;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class WishListTest extends TokenTestCase
{
    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const OTHER_PASSWORD = 'useruser';

    private const OTHER_USER_OXID = '245ad3b5380202966df6ff128e9eecaq';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const OTHER_PRODUCT_ID = '_test_product_for_rating_avg';

    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRIVATE_WISHLIST_ID = 'test_make_wishlist_private';

    protected function setUp(): void
    {
        parent::setUp();

        $this->getWishList()->delete();
    }

    public function testAddProductToWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);

        $result = $this->addProductToWishListMutation(self::PRODUCT_ID);
        $this->assertResponseStatus(200, $result);
        $this->assertNotEmpty($result['body']['data']['wishListAddProduct']['id']);

        $products = $this->getWishListArticles();
        $this->assertSame(self::PRODUCT_ID, array_pop($products)->getId());
    }

    public function testAddMultipleProductsToWishList(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::OTHER_PASSWORD);
        $this->addProductToWishListMutation(self::PRODUCT_ID);
        $this->addProductToWishListMutation(self::OTHER_PRODUCT_ID);

        $products = $this->getWishListArticles();
        $this->assertSame(2, count($products));
    }

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

    private function getWishList(): EshopUserBasket
    {
        $user = oxNew(EshopUser::class);
        $user->load(self::OTHER_USER_OXID);

        return $user->getBasket('wishlist');
    }

    private function getWishListArticles(): array
    {
        return $this->getWishList()->getArticles();
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
}
