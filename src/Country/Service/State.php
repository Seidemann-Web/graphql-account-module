<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Service;

use OxidEsales\GraphQL\Account\Country\DataType\State as StateDataType;
use OxidEsales\GraphQL\Account\Country\DataType\StateFilterList;
use OxidEsales\GraphQL\Account\Country\Exception\StateNotFound;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class State
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository      = $repository;
    }

    /**
     * @return StateDataType[]
     */
    public function states(StateFilterList $filter): array
    {
        return $this->repository->getByFilter(
            $filter,
            StateDataType::class
        );
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
