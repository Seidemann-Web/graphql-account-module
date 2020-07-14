<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
use OxidEsales\GraphQL\Account\Account\Service\Address as AddressService;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Base\Service\Authentication;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Customer
{
    /** @var CustomerService */
    private $customerService;

    /** @var Authentication */
    private $authenticationService;

    /** @var AddressService */
    private $addressService;

    public function __construct(
        CustomerService $customerService,
        Authentication $authenticationService,
        AddressService $addressService
    ) {
        $this->customerService       = $customerService;
        $this->authenticationService = $authenticationService;
        $this->addressService        = $addressService;
    }

    /**
     * @Query()
     * @Logged()
     */
    public function customer(): CustomerDataType
    {
        return $this->customerService->customer(
            $this->authenticationService->getUserId()
        );
    }

    /**
     * @Mutation()
     */
    public function customerInvoiceAddressSet(
        InvoiceAddress $invoiceAddress
    ): InvoiceAddress {
        return $this->addressService->updateInvoiceAddress($invoiceAddress);
    }
}
