<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use DateTimeImmutable;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
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

    public function __construct(
        Authentication $authenticationService,
        CustomerService $customerService
    ) {
        $this->authenticationService = $authenticationService;
        $this->customerService       = $customerService;
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
        ID $countryID,
        ?string $vatID,
        ?string $phone,
        ?string $mobile,
        ?string $fax,
        DateTimeImmutable $creationDate
    ): InvoiceAddress {
        /** @var User $customer */
        $customer = $this->customerService
            ->customer($this->authenticationService->getUserId())
            ->getEshopModel();

        $customer->assign([
            'oxsal'       => $salutation,
            'oxfname'     => $firstname,
            'oxlname'     => $lastname,
            'oxcompany'   => $company ?: $customer->getFieldData('oxcompany'),
            'oxaddinfo'   => $additionalInfo ?: $customer->getFieldData('oxaddinfo'),
            'oxstreet'    => $street,
            'oxstreetnr'  => $streetNumber,
            'oxzip'       => $zipCode,
            'oxcity'      => $city,
            'oxcountryid' => $countryID,
            'oxustid'     => $vatID ?: $customer->getFieldData('oxustid'),
            'oxprivphone' => $phone ?: $customer->getFieldData('oxprivphone'),
            'oxmobfone'   => $mobile ?: $customer->getFieldData('oxmobfone'),
            'oxfax'       => $fax ?: $customer->getFieldData('oxfax'),
            'oxcreate'    => $creationDate->format('Y-m-d'),
        ]);

        return new InvoiceAddress($customer);
    }
}
