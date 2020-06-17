<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Controller;

use OxidEsales\GraphQL\Account\Rating\DataType\Rating as RatingType;
use OxidEsales\GraphQL\Account\Rating\DataType\RatingFilterList;
use OxidEsales\GraphQL\Account\Rating\Service\Rating as RatingService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Rating
{
    /** @var RatingService */
    private $ratingService;

    public function __construct(
        RatingService $ratingService
    ) {
        $this->ratingService = $ratingService;
    }

    /**
     * @Query()
     */
    public function rating(string $id): RatingType
    {
        return $this->ratingService->rating($id);
    }

    /**
     * @Query()
     *
     * @return RatingType[]
     */
    public function ratings(): array
    {
        return $this->ratingService->ratings(
            new RatingFilterList()
        );
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function ratingSet(RatingType $rating): RatingType
    {
        $this->ratingService->save($rating);

        return $rating;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function ratingDelete(string $id): RatingType
    {
        return $this->ratingService->delete($id);
    }
}
