<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Service;

use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Rating
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Authorization */
    private $authorizationService;

    /** @var RelationService */
    private $ratingRelationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService,
        RelationService $ratingRelationService
    ) {
        $this->repository            = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService  = $authorizationService;
        $this->ratingRelationService = $ratingRelationService;
    }
}
