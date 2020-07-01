<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Controller;

use OxidEsales\GraphQL\Account\WishList\DataType\WishList as WishListDataType;
use OxidEsales\GraphQL\Account\WishList\Service\WishList as WishListService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

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
     * @Mutation
     * @Logged
     */
    public function wishListAddProduct(string $productId): WishListDataType
    {
        return $this->wishListService->addProduct($productId);
    }

    public function wishListMakePrivate(): WishListDataType
    {
        return $this->wishListService->makePrivate();
    }
}
