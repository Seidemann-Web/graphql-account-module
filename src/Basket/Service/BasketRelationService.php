<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Service;

use OxidEsales\GraphQL\Account\Account\DataType\Customer;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket;
use OxidEsales\GraphQL\Account\Basket\DataType\BasketItem;
use OxidEsales\GraphQL\Account\Basket\DataType\BasketItemFilterList;
use OxidEsales\GraphQL\Account\Basket\Service\BasketItem as BasketItemService;
use OxidEsales\GraphQL\Base\DataType\IDFilter;
use OxidEsales\GraphQL\Base\DataType\PaginationFilter;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=Basket::class)
 */
final class BasketRelationService
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
    public function customer(Basket $basket): Customer
    {
        return $this->customerService->basketOwner((string) $basket->getUserId());
    }

    /**
     * @Field()
     *
     * @return BasketItem[]
     */
    public function items(
        Basket $basket,
        ?PaginationFilter $pagination
    ): array {
        return $this->basketItemService->basketItems(
            new BasketItemFilterList(
                new IDFilter($basket->id())
            ),
            $pagination
        );
    }
}
