<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Controller;

use OxidEsales\GraphQL\Account\Account\Exception\CustomerNotFound;
use OxidEsales\GraphQL\Account\WishList\DataType\WishList as WishListDataType;
use OxidEsales\GraphQL\Account\WishList\Exception\WishListNotFound;
use OxidEsales\GraphQL\Account\WishList\Service\WishList as WishListService;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
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
     * @Mutation()
     * @Logged()
     */
    public function wishListAddProduct(string $productId): WishListDataType
    {
        return $this->wishListService->addProduct($productId);
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function wishListMakePrivate(): WishListDataType
    {
        return $this->wishListService->makePrivate();
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function wishListMakePublic(): WishListDataType
    {
        return $this->wishListService->makePublic();
    }

    /**
     * @Query()
     */
    public function wishList(string $id): WishListDataType
    {
        return $this->wishListService->wishList($id);
    }

    /**
     * @Query()
     *
     * @throws CustomerNotFound
     * @throws InvalidLogin
     * @throws InvalidToken
     * @throws WishListNotFound
     */
    public function wishListByOwnerId(string $ownerId): WishListDataType
    {
        return $this->wishListService->wishListByOwnerId($ownerId);
    }
}
