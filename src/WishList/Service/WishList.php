<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Service;

use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\Exception\CustomerNotFound;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Account\Account\Service\RelationService as CustomerRelationService;
use OxidEsales\GraphQL\Account\WishList\DataType\WishList as WishListDataType;
use OxidEsales\GraphQL\Account\WishList\Exception\WishListNotFound;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Legacy;
use OxidEsales\GraphQL\Catalogue\Product\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Product\Service\Product as CatalogueProductService;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class WishList
{
    public const SHOP_WISH_LIST_NAME = 'wishlist';

    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Legacy */
    private $legacyService;

    /** @var CustomerService */
    private $customerService;

    /** @var CatalogueProductService */
    private $productService;

    /** @var CustomerRelationService */
    private $customerRelationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService,
        Legacy $legacyService,
        CustomerService $customerService,
        CustomerRelationService $customerRelationService,
        CatalogueProductService $productService
    ) {
        $this->repository              = $repository;
        $this->authenticationService   = $authenticationService;
        $this->authorizationService    = $authorizationService;
        $this->legacyService           = $legacyService;
        $this->customerService         = $customerService;
        $this->customerRelationService = $customerRelationService;
        $this->productService          = $productService;
    }

    public function addProduct(string $productId): WishListDataType
    {
        $this->assertProductId($productId);
        /** @var CustomerDataType $customer */
        $customer = $this->customerService->customer($this->authenticationService->getUserId());

        /** @var EshopUserBasketModel $wishListBasket */
        $wishListBasket = $customer->getEshopModel()->getBasket(self::SHOP_WISH_LIST_NAME);
        $wishListBasket->addItemToBasket($productId, 1);

        return new WishListDataType($wishListBasket);
    }

    public function makePrivate(): WishListDataType
    {
        /** @var CustomerDataType $customer */
        $customer = $this->customerService->customer($this->authenticationService->getUserId());
        $wishList = $customer->getWishList();
        $wishList->setPublic(false);

        return $wishList;
    }

    public function makePublic(): WishListDataType
    {
        /** @var CustomerDataType $customer */
        $customer = $this->customerService->customer($this->authenticationService->getUserId());
        $wishList = $customer->getWishList();
        $wishList->setPublic(true);

        return $wishList;
    }

    /**
     * @throws WishListNotFound
     */
    public function wishList(string $id): WishListDataType
    {
        try {
            /** @var WishListDataType $wishList */
            $wishList = $this->repository->getById(
                $id,
                WishListDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw WishListNotFound::byId($id);
        }

        if ($wishList->isPublic() === false && !$this->isSameUser($wishList)) {
            throw new InvalidToken('Wish list is private.');
        }

        return $wishList;
    }

    /**
     * @throws CustomerNotFound
     * @throws InvalidLogin
     * @throws InvalidToken
     * @throws WishListNotFound
     */
    public function wishListByOwnerId(string $customerId): WishListDataType
    {
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        $customer = $this->customerService->wishListOwner($customerId);
        $wishList = $this->customerRelationService->getWishList($customer);

        if (!$wishList->isPublic() && !$this->isSameUser($wishList)) {
            throw WishListNotFound::byOwnerId($customerId);
        }

        return $wishList;
    }

    /**
     * @throws ProductNotFound
     *
     * @return true
     */
    private function assertProductId(string $productId): bool
    {
        try {
            $this->productService->product($productId);
        } catch (NotFound $e) {
            throw ProductNotFound::byId($productId);
        }

        return true;
    }

    private function isSameUser(WishListDataType $wishList): bool
    {
        return (string) $wishList->getUserId() === (string) $this->authenticationService->getUserId();
    }
}
