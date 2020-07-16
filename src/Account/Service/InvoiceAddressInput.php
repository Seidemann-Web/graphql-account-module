<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
use OxidEsales\GraphQL\Account\Account\Infrastructure\InvoiceAddressFactory;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Base\Service\Authentication;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

final class InvoiceAddressInput
{
    /** @var Authentication */
    private $authenticationService;

    /** @var CustomerService */
    private $customerService;

    /** @var InvoiceAddressFactory */
    private $invoiceAddressFactory;

    public function __construct(
        InvoiceAddressFactory $invoiceAddressFactory,
        Authentication $authenticationService,
        CustomerService $customerService
    ) {
        $this->invoiceAddressFactory = $invoiceAddressFactory;
        $this->authenticationService = $authenticationService;
        $this->customerService       = $customerService;
    }

    /**
     * @Factory(name="InvoiceAddressInput")
     */
    public function fromUserInput(
        ?string $salutation = null,
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $company = null,
        ?string $additionalInfo = null,
        ?string $street = null,
        ?string $streetNumber = null,
        ?string $zipCode = null,
        ?string $city = null,
        ?ID $countryId = null,
        ?string $vatID = null,
        ?string $phone = null,
        ?string $mobile = null,
        ?string $fax = null
    ): InvoiceAddress {
        $customer = $this->customerService
            ->customer($this->authenticationService->getUserId());

        return $this->invoiceAddressFactory->createValidInvoiceAddressType(
            $customer,
            $salutation,
            $firstname,
            $lastname,
            $company,
            $additionalInfo,
            $street,
            $streetNumber,
            $zipCode,
            $city,
            $countryId,
            $vatID,
            $phone,
            $mobile,
            $fax
        );
    }
}
