<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Controller;

use OxidEsales\GraphQL\Account\Rating\Service\Rating as RatingService;

final class Rating
{
    /** @var RatingService */
    private $ratingService;

    public function __construct(
        RatingService $ratingService
    ) {
        $this->ratingService = $ratingService;
    }
}
