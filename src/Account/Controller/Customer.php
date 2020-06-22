<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerType;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Base\Service\Authentication;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Customer
{
    /** @var CustomerService */
    private $customerService;

    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        CustomerService $customerService,
        Authentication $authenticationService
    ) {
        $this->customerService       = $customerService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @Query()
     * @Logged()
     */
    public function me(): CustomerType
    {
        return $this->customerService->customer(
            $this->authenticationService->getUserId()
        );
    }
}
