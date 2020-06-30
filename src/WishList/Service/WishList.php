<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Service;

use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Account\WishList\DataType\WishList as WishListDataType;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Base\Service\Legacy;
use OxidEsales\GraphQL\Catalogue\Product\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Product\Service\Product as CatalogueProductService;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class WishList
{
    private const SHOP_WISH_LIST_NAME = 'wishlist';

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

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Legacy $legacyService,
        CustomerService $customerService,
        CatalogueProductService $productService
    ) {
        $this->repository             = $repository;
        $this->authenticationService  = $authenticationService;
        $this->legacyService          = $legacyService;
        $this->customerService        = $customerService;
        $this->productService         = $productService;
    }

    public function addProduct(string $productId): WishListDataType
    {
        $this->assertProductId($productId);
        /** @var CustomerDataType $customer */
        $customer = $this->customerService->customer($this->authenticationService->getUserId());

        /** @var EshopUserBasketModel $wishListBasket */
        $wishListBasket = $customer->getEshopModel()->getBasket(self::SHOP_WISH_LIST_NAME);
        $wishListBasket->addItemToBasket($productId, 1);
        $wishListBasket->getItemCount(true);

        return new WishListDataType($wishListBasket);
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
}
