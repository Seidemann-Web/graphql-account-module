<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Service;

use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Country\DataType\State as StateDataType;
use OxidEsales\GraphQL\Account\Country\Exception\StateNotFound;
use OxidEsales\GraphQL\Account\Country\Infrastructure\Repository as StateRepository;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class State
{
    /** @var StateRepository */
    private $stateRepository;

    /** @var Repository */
    private $repository;

    public function __construct(
        StateRepository $stateRepository,
        Repository $repository
    ) {
        $this->stateRepository = $stateRepository;
        $this->repository      = $repository;
    }

    /**
     * @return StateDataType[]
     */
    public function statesByCountry(CountryDataType $country): array
    {
        return $this->stateRepository->statesByCountry($country);
    }

    public function state(string $id): StateDataType
    {
        try {
            /** @var StateDataType $state */
            $state = $this->repository->getById(
                $id,
                StateDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw StateNotFound::byId($id);
        }

        return $state;
    }
}
