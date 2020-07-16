<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Service;

use OxidEsales\GraphQL\Account\Account\DataType\Customer;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Account\Shared\DataType\BasketItem;
use OxidEsales\GraphQL\Account\Shared\DataType\BasketItemFilterList;
use OxidEsales\GraphQL\Account\Shared\Service\BasketItem as BasketItemService;
use OxidEsales\GraphQL\Account\WishList\DataType\WishList;
use OxidEsales\GraphQL\Base\DataType\IDFilter;
use OxidEsales\GraphQL\Base\DataType\PaginationFilter;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=WishList::class)
 */
final class RelationService
{
    /** @var BasketItemService */
    private $basketItemService;

    /** @var CustomerService */
    private $customerService;

    public function __construct(
        BasketItemService $basketItemService,
        CustomerService $customerService
    ) {
        $this->basketItemService  = $basketItemService;
        $this->customerService    = $customerService;
    }

    /**
     * @Field()
     */
    public function getCustomer(WishList $wishList): Customer
    {
        return $this->customerService->wishListOwner((string) $wishList->getUserId());
    }

    /**
     * @Field()
     *
     * @return BasketItem[]
     */
    public function getBasketItems(
        WishList $wishList,
        ?PaginationFilter $pagination
    ): array {
        return $this->basketItemService->basketItems(
            new BasketItemFilterList(
                new IDFilter($wishList->getId())
            ),
            $pagination
        );
    }
}
