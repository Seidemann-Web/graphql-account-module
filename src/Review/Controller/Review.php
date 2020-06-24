<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Review\Controller;

use OxidEsales\GraphQL\Catalogue\Review\DataType\Review as ReviewType;
use OxidEsales\GraphQL\Catalogue\Review\Service\Review as ReviewService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class Review
{
    /** @var ReviewService */
    private $reviewService;

    public function __construct(
        ReviewService $reviewService
    ) {
        $this->reviewService = $reviewService;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function reviewSet(ReviewType $review): ReviewType
    {
        $this->reviewService->save($review);

        return $review;
    }
}
