<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Services;

use OxidEsales\GraphQL\Account\Rating\DataType\Rating as RatingType;
use OxidEsales\GraphQL\Account\Rating\DataType\RatingFilterList;
use OxidEsales\GraphQL\Account\Rating\Exception\RatingNotFound;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Service\Repository;

final class Rating
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @throws RatingNotFound
     */
    public function rating(string $id): RatingType
    {
        /** Only logged in users can query ratings */
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        try {
            /** @var RatingType $rating */
            $rating = $this->repository->getById(
                $id,
                RatingType::class
            );
        } catch (NotFound $e) {
            throw RatingNotFound::byId($id);
        }

        /** If the logged in user is authorized return the rating */
        if ($this->authorizationService->isAllowed('VIEW_RATINGS')) {
            return $rating;
        }

        /** A user can query only its own rating */
        if (!$this->isSameUser($rating)) {
            throw new InvalidLogin('Unauthorized');
        }
        return $rating;
    }

    /**
     * @return RatingType[]
     */
    public function ratings(RatingFilterList $filterList): array
    {
        return $this->repository->getByFilter(
            $filterList->withUserFilter(
                new StringFilter(
                    $this->authenticationService->getUserId()
                )
            ),
            RatingType::class
        );
    }

    public function save(RatingType $rating): bool
    {
        $modelItem = $rating->getEshopModel();
        return $this->repository->saveModel($modelItem);
    }

    private function isSameUser(RatingType $rating): bool
    {
        return ($rating->getUserId() == $this->authenticationService->getUserId());
    }
}
