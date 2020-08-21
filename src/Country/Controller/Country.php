<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Controller;

use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Country\DataType\CountryFilterList;
use OxidEsales\GraphQL\Account\Country\DataType\CountrySorting;
use OxidEsales\GraphQL\Account\Country\Service\Country as CountryService;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Country
{
    /** @var CountryService */
    private $countryService;

    public function __construct(
        CountryService $countryService
    ) {
        $this->countryService = $countryService;
    }

    /**
     * @Query()
     */
    public function country(string $id): CountryDataType
    {
        return $this->countryService->country($id);
    }

    /**
     * @Query()
     *
     * @return CountryDataType[]
     */
    public function countries(
        ?CountryFilterList $filter = null,
        ?CountrySorting $sort = null
    ): array {
        return $this->countryService->countries(
            $filter ?? new CountryFilterList(),
            $sort ?? new CountrySorting([])
        );
    }
}
