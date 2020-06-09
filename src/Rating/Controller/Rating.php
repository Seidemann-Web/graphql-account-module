<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Controller;

use OxidEsales\GraphQL\Account\Rating\Services\Rating as RatingService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use OxidEsales\GraphQL\Account\Rating\DataType\Rating as RatingType;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Rating
{
    /** @var RatingService */
    private $ratingService = null;

    public function __construct(
        RatingService $ratingService
    ) {
        $this->ratingService = $ratingService;
    }

    /**
     * @Query()
     *
     * @return RatingType[]
     */
    public function ratings(): array
    {
        return $this->ratingService->reviews();
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function ratingSet(RatingType $ratingInput): RatingType
    {
        $this->ratingService->save($ratingInput);

        return $ratingInput;
    }
}
