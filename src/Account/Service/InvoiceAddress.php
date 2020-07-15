<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress as InvoiceAddressDataType;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class InvoiceAddress
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

    public function customerInvoiceAddress(): InvoiceAddressDataType
    {
        return $this->repository->getById(
            $this->authenticationService->getUserId(),
            InvoiceAddressDataType::class
        );
    }
}
