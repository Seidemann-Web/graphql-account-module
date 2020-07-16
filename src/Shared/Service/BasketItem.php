<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Shared\Service;

use OxidEsales\GraphQL\Account\Shared\DataType\BasketItem as BasketItemDataType;
use OxidEsales\GraphQL\Account\Shared\DataType\BasketItemFilterList;
use OxidEsales\GraphQL\Base\DataType\PaginationFilter;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class BasketItem
{
    /** @var Repository */
    private $repository;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authorization $authorizationService
    ) {
        $this->repository           = $repository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @return BasketItemDataType[]
     */
    public function basketItems(BasketItemFilterList $filter, ?PaginationFilter $pagination = null): array
    {
        return $this->repository->getByFilter(
            $filter,
            BasketItemDataType::class,
            $pagination
        );
    }
}
