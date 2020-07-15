<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Account\Infrastructure\AddressFactory;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

final class DeliveryAddressInput
{
    /** @var AddressFactory */
    private $addressFactory;

    public function __construct(
        AddressFactory $addressFactory
    ) {
        $this->addressFactory = $addressFactory;
    }

    /**
     * @Factory(name="DeliveryAddressInput")
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
        ?string $phone = null,
        ?string $fax = null
    ): DeliveryAddressDataType {
        return $this->addressFactory->createValidAddressType(
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
            $phone,
            $fax
        );
    }
}
