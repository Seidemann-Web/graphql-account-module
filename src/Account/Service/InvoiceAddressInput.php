<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
//use OxidEsales\GraphQL\Account\Country\DataType\Country;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Base\Service\Authentication;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use DateTimeImmutable;

final class InvoiceAddressInput
{
    /** @var Authentication */
    private $authenticationService;

    /** @var CustomerService */
    private $customerService;

    public function __construct(
        Authentication $authenticationService,
        CustomerService $customerService
    ) {
        $this->authenticationService = $authenticationService;
        $this->customerService = $customerService;
    }

    /**
     * @Factory
     */
    public function fromUserInput(
        string $salutation,
        string $firstname,
        string $lastname,
        ?string $company,
        ?string $additionalInfo,
        string $street,
        string $streetNumber,
        string $zipCode,
        string $city,
//        Country $country,
        ?string $vatID,
        ?string $phone,
        ?string $mobile,
        ?string $fax,
        DateTimeImmutable $creationDate
    ): InvoiceAddress
    {
        /** @var User $customer */
        $customer = $this->customerService->customer($this->authenticationService->getUserId())->getEshopModel();

        //todo: create mapping
        $customer->assign([
           'oxsal' => $salutation
        ]);

        return new InvoiceAddress($customer);
    }
}
