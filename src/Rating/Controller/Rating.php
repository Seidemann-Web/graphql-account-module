<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Controller;

use OxidEsales\GraphQL\Account\Rating\Services\Rating as RatingService;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Controller\Base;
use OxidEsales\GraphQL\Catalogue\Service\Repository;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use OxidEsales\GraphQL\Catalogue\DataType\Rating as RatingType;

class Rating extends Base
{
    /** @var RatingService */
    private $ratingService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService,
        RatingService $ratingService
    ) {
        parent::__construct($repository, $authenticationService, $authorizationService);

        $this->ratingService = $ratingService;
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
