<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\Eshop\Application\Model\Address as EshopAddressModel;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

final class DeliveryAddressInput
{
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
        return new DeliveryAddressDataType(
            new EshopAddressModel()
        );
    }
}
