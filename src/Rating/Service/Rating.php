<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Service;

use OxidEsales\GraphQL\Account\Rating\DataType\Rating as RatingDataType;
use OxidEsales\GraphQL\Account\Rating\DataType\RatingFilterList;
use OxidEsales\GraphQL\Account\Rating\Exception\RatingExists;
use OxidEsales\GraphQL\Account\Rating\Exception\RatingNotFound;
use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
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

    /**
     * @throws InvalidLogin
     * @throws RatingNotFound
     */
    public function rating(string $id): RatingDataType
    {
        /** Only logged in users can query ratings */
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        try {
            /** @var RatingDataType $rating */
            $rating = $this->repository->getById(
                $id,
                RatingDataType::class,
                false
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
     * @return RatingDataType[]
     */
    public function ratings(RatingFilterList $filterList): array
    {
        return $this->repository->getByFilter(
            $filterList->withUserFilter(
                new StringFilter(
                    $this->authenticationService->getUserId()
                )
            ),
            RatingDataType::class
        );
    }

    public function save(RatingDataType $rating): bool
    {
        if ($this->productRatingExists($rating)) {
            throw RatingExists::byId((string) $rating->getObjectId()->val());
        }

        $modelItem = $rating->getEshopModel();

        $response = $this->repository->saveModel($modelItem);

        // When new rating is added, product's average rating should be recalculated
        if ($product = $this->ratingRelationService->product($rating)) {
            $product->getEshopModel()->addToRatingAverage($rating->getRating());
        }

        return $response;
    }

    /**
     * @throws InvalidLogin
     * @throws RatingNotFound
     */
    public function delete(string $id): bool
    {
        $rating = $this->rating($id);

        //we got this far, we have a user
        //user can delete only its own rating, admin can delete any rating
        if (
            $this->authorizationService->isAllowed('DELETE_RATING')
            || $this->isSameUser($rating)
        ) {
            $deleted = $this->repository->delete($rating->getEshopModel());
        } else {
            throw new InvalidLogin('Unauthorized');
        }

        return $deleted;
    }

    private function isSameUser(RatingDataType $rating): bool
    {
        return (string) $rating->getUserId() === (string) $this->authenticationService->getUserId();
    }

    private function productRatingExists(RatingDataType $rating): bool
    {
        $filterList = new RatingFilterList(
            new StringFilter((string) $rating->getUserId()->val()),
            new StringFilter((string) $rating->getObjectId()->val())
        );
        $ratings = $this->ratings($filterList);

        return !empty($ratings);
    }
}
