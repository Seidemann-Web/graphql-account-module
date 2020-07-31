<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Review\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class ReviewMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCT_ID_SHOP_1 = '_test_product_wp1_';

    private const PRODUCT_ID_SHOP_2 = '_test_product_wp2_';

    private const PRODUCT_ID_BOTH_SHOPS = '_test_product_for_rating_5_';

    private const TEXT = 'shiny nice review text';

    private $createdReviews = [];

    protected function tearDown(): void
    {
        foreach ($this->createdReviews as $id) {
            $this->reviewDelete($id);
        }

        parent::tearDown();
    }

    public function dataProviderReviewPerShop(): array
    {
        return [
            'shop1' => [
                1,
                self::PRODUCT_ID_SHOP_1,
                200,
            ],
            'shop2' => [
                2,
                self::PRODUCT_ID_SHOP_2,
                200,
            ],
            'shop1_with_shop2product' => [
                1,
                self::PRODUCT_ID_SHOP_2,
                404,
            ],
            'shop2_with_inheritedproduct' => [
                2,
                self::PRODUCT_ID_BOTH_SHOPS,
                200,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderReviewPerShop
     */
    public function testReviewPerShop(string $shopId, string $productId, int $expected): void
    {
        $this->ensureShop((int) $shopId);
        EshopRegistry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', true);
        EshopRegistry::getConfig()->setShopId($shopId);
        $this->setGETRequestParameter('shp', $shopId);

        //this user exists in shop 1 and shop 2 with different oxid
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->reviewSet($productId);
        $this->assertResponseStatus($expected, $result);

        $retry = $this->reviewSet($productId);
        $this->assertResponseStatus(404, $retry);

        if (isset($result['body']['data']['reviewSet']['id'])) {
            $this->createdReviews[] = $result['body']['data']['reviewSet']['id'];
        }
    }

    public function testMallUserReview(): void
    {
        $this->ensureShop(1);
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        EshopRegistry::getConfig()->setConfigParam('blAllowUsersToManageTheirReviews', true);

        //let mall user create some reviews in shop 1
        EshopRegistry::getConfig()->setShopId(1);
        $this->setGETRequestParameter('shp', '1');
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);

        $result = $this->reviewSet(self::PRODUCT_ID_SHOP_1);
        $this->assertResponseStatus(200, $result);
        $this->createdReviews[] = $result['body']['data']['reviewSet']['id'];
        $result                 = $this->reviewSet(self::PRODUCT_ID_BOTH_SHOPS);
        $this->assertResponseStatus(200, $result);
        $this->createdReviews[] = $result['body']['data']['reviewSet']['id'];

        //let mall user set a review for same product in subshop
        EshopRegistry::getConfig()->setShopId(2);
        $this->setGETRequestParameter('shp', '2');
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);

        //user already did give a review in subshop 1 so he cannot add a another one for same product
        $result = $this->reviewSet(self::PRODUCT_ID_BOTH_SHOPS);
        $this->assertResponseStatus(404, $result);

        //review another product
        $result = $this->reviewSet(self::PRODUCT_ID_SHOP_2);
        $this->assertResponseStatus(200, $result);
        $this->createdReviews[] = $result['body']['data']['reviewSet']['id'];q

        //get reviews for subshop
        $allReviews = $this->getReviews(false);
        $this->assertResponseStatus(200, $allReviews);
        $this->assertEquals(3, count($allReviews['body']['data']['customer']['reviews']));

        //Here we have the case, that one of the products is not available in the subshop
        //The result is fine but we get a 404for missing product
        $allReviews = $this->getReviews(true);
        $this->assertResponseStatus(404, $allReviews);
    }

    private function reviewSet(string $productId): array
    {
        return $this->query(
            'mutation {
                reviewSet(review: {
                    productId: "' . $productId . '",
                    text: "' . self::TEXT . '",
                    rating: 5
                }){
                    id
                    product{
                        id
                    }
                    text
                    rating
                }
            }'
        );
    }

    private function reviewDelete(string $id): void
    {
        $result = $this->query(
            'mutation {
                     reviewDelete(id: "' . $id . '")
                }'
        );
        $this->assertResponseStatus(200, $result);
    }

    private function getReviews(bool $queryProducts)
    {
        $query = 'query {
                customer {
                    reviews {
                        id
                 ';

        if ($queryProducts) {
            $query .= ' product {
                            id
                        }';
        }
        $query .= '  }
                }
            }';

        return $this->query($query);
    }
}
