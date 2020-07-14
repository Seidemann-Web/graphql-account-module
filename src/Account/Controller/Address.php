<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
use OxidEsales\GraphQL\Account\Account\Service\Address as AddressService;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Logged;

final class Address
{
    /** @var AddressService */
    private $addressService;

    public function __construct(
        AddressService $addressService
    ) {
        $this->addressService        = $addressService;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function customerInvoiceAddressSet(
        InvoiceAddress $invoiceAddress
    ): InvoiceAddress {
        return $this->addressService->updateInvoiceAddress($invoiceAddress);
    }
}
