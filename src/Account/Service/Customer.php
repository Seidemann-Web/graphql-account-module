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
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Customer
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService
    ) {
        $this->repository            = $repository;
        $this->authenticationService = $authenticationService;
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

        try {
            /** @var CustomerDataType $customer */
            $customer = $this->repository->getById(
                $id,
                CustomerDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw CustomerNotFound::byId($id);
        }

        return $customer;
    }
}
