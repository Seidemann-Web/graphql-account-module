<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Controller;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as WishListDataType;
use OxidEsales\GraphQL\Account\WishList\Service\WishList as WishListService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class WishList
{
    /** @var WishListService */
    private $wishListService;

    public function __construct(
        WishListService $wishListService
    ) {
        $this->wishListService = $wishListService;
    }

    /**
     * @Query()
     */
    public function wishList(string $id): WishListDataType
    {
        return $this->wishListService->wishList($id);
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function wishListRemoveProduct(string $productId): WishListDataType
    {
        return $this->wishListService->removeProduct($productId);
    }
}
