<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Account\Infrastructure\AddressFactory;
use OxidEsales\GraphQL\Base\Service\Authentication;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

final class DeliveryAddressInput
{
    /** @var AddressFactory */
    private $addressFactory;

    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        AddressFactory $addressFactory,
        Authentication $authenticationService
    ) {
        $this->addressFactory        = $addressFactory;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @Factory(name="DeliveryAddressInput")
     */
    public function fromUserInput(
        ?string $salutation = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null,
        ?string $additionalInfo = null,
        ?string $street = null,
        ?string $streetNumber = null,
        ?string $zipCode = null,
        ?string $city = null,
        ?ID $countryId = null,
        ?string $phone = null,
        ?string $fax = null
    ): DeliveryAddressDataType {
        return $this->addressFactory->createValidAddressType(
            $this->authenticationService->getUserId(),
            $salutation,
            $firstName,
            $lastName,
            $company,
            $additionalInfo,
            $street,
            $streetNumber,
            $zipCode,
            $city,
            $countryId,
            $phone,
            $fax
        );
    }
}
