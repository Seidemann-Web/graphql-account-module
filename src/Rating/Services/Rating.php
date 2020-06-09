<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Services;

use OxidEsales\GraphQL\Account\Rating\DataType\Rating as RatingType;
use OxidEsales\GraphQL\Account\Rating\DataType\RatingFilterList;
use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Service\Repository;

class Rating
{
    /** @var Repository */
    private $repository;
    /**
     * @var Authentication
     */
    private $authenticationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return RatingType[]
     */
    public function reviews(?RatingFilterList $filterList = null): array
    {

        return $this->repository->getByFilter(
            $filterList ?? new RatingFilterList(
                new StringFilter($this->authenticationService->getUserId())
            ),
            RatingType::class
        );
    }

    public function save(RatingType $rating): bool
    {
        $modelItem = $rating->getEshopModel();
        return $this->repository->saveModel($modelItem);
    }
}
