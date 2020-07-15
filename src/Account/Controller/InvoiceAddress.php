<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress as InvoiceAddressDataType;
use OxidEsales\GraphQL\Account\Account\Service\InvoiceAddress as InvoiceAddressService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class InvoiceAddress
{
    /** @var InvoiceAddressService */
    private $invoiceAddressService;

    public function __construct(
        InvoiceAddressService $invoiceAddressService
    ) {
        $this->invoiceAddressService = $invoiceAddressService;
    }

    /**
     * @Query()
     * @Logged()
     */
    public function customerInvoiceAddress(): InvoiceAddressDataType
    {
        return $this->invoiceAddressService->customerInvoiceAddress();
    }
}
