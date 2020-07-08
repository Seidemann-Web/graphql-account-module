<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Service;

use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Country\DataType\CountryFilterList;
use OxidEsales\GraphQL\Account\Country\Exception\CountryNotFound;
use OxidEsales\GraphQL\Base\DataType\BoolFilter;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Country
{
    /** @var Repository */
    private $repository;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authorization $authorizationService
    ) {
        $this->repository            = $repository;
        $this->authorizationService  = $authorizationService;
    }

    /**
     * @throws InvalidLogin
     * @throws CountryNotFound
     */
    public function country(string $id): CountryDataType
    {
        try {
            /** @var CountryDataType $country */
            $country = $this->repository->getById(
                $id,
                CountryDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw CountryNotFound::byId($id);
        }

        if ($country->isActive()) {
            return $country;
        }

        if (!$this->authorizationService->isAllowed('VIEW_INACTIVE_COUNTRY')) {
            throw new InvalidLogin('Unauthorized');
        }

        return $country;
    }

    /**
     * @return CountryDataType[]
     */
    public function countries(CountryFilterList $filter): array
    {
        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_COUNTRY')) {
            $filter = $filter->withActiveFilter(null);
        }

        return $this->repository->getByFilter($filter, CountryDataType::class);
    }
}
