<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\Exception\CustomerNotFound;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Legacy;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Customer
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Legacy */
    private $legacyService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Legacy $legacyService
    ) {
        $this->repository            = $repository;
        $this->authenticationService = $authenticationService;
        $this->legacyService         = $legacyService;
    }

    /**
     * @throws InvalidLogin
     * @throws CustomerNotFound
     */
    public function customer(string $id): CustomerDataType
    {
        if ((string) $id !== (string) $this->authenticationService->getUserId()) {
            throw new InvalidLogin('Unauthorized');
        }

        $ignoreSubshop = (bool) $this->legacyService->getConfigParam('blMallUsers');

        try {
            /** @var CustomerDataType $customer */
            $customer = $this->repository->getById(
                $id,
                CustomerDataType::class,
                $ignoreSubshop
            );
        } catch (NotFound $e) {
            throw CustomerNotFound::byId($id);
        }

        return $customer;
    }

    /**
     * @throws CustomerNotFound
     */
    public function wishListOwner(string $id): CustomerDataType
    {
        $ignoreSubShop = (bool) $this->legacyService->getConfigParam('blMallUsers');

        try {
            /** @var CustomerDataType $customer */
            $customer = $this->repository->getById(
                $id,
                CustomerDataType::class,
                $ignoreSubShop
            );
        } catch (NotFound $e) {
            throw CustomerNotFound::byId($id);
        }

        return $customer;
    }
}
