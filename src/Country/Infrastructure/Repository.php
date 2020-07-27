<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Infrastructure;

use OxidEsales\Eshop\Application\Model\State as EshopStateModel;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Country\DataType\State as StateDataType;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as SharedRepository;

final class Repository
{
    /** @var SharedRepository */
    private $sharedRepository;

    public function __construct(
        SharedRepository $sharedRepository
    ) {
        $this->sharedRepository = $sharedRepository;
    }

    /**
     * @return StateDataType[]
     */
    public function statesByCountry(CountryDataType $country): array
    {
        $states    = [];
        $stateList = $country->getEshopModel()->getStates();
        /** @var EshopStateModel $state */
        foreach ($stateList as $state) {
            $states[] = $this->sharedRepository->getById(
                $state->getId(),
                StateDataType::class,
                false
            );
        }

        return $states;
    }
}
