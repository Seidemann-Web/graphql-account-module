<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Services;

use OxidEsales\GraphQL\Account\Rating\Exception\RatingOutOfBounds;
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
        $ratingValue = $rating->getRating();
        if ($ratingValue < 1 || $ratingValue > 5) {
            throw new RatingOutOfBounds();
        }

        $modelItem = $rating->getEshopModel();
        return $this->repository->saveModel($modelItem);
    }
}
