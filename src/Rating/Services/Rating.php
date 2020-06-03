<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Services;

use OxidEsales\GraphQL\Catalogue\DataType\Rating as RatingType;
use OxidEsales\GraphQL\Catalogue\Service\Repository;

class Rating
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function save(RatingType $rating): bool
    {
        $modelItem = $rating->getEshopModel();
        return $this->repository->saveModel($modelItem);
    }
}
