<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Service;

use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Country\DataType\State as StateDataType;
use OxidEsales\GraphQL\Account\Country\Infrastructure\Repository as StateRepository;

final class State
{
    /** @var StateRepository */
    private $stateRepository;

    public function __construct(
        StateRepository $stateRepository
    ) {
        $this->stateRepository = $stateRepository;
    }

    /**
     * @return StateDataType[]
     */
    public function statesByCountry(CountryDataType $country): array
    {
        return $this->stateRepository->statesByCountry($country);
    }
}
