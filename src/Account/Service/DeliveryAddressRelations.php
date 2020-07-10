<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Country\DataType\Country;
use OxidEsales\GraphQL\Account\Country\Service\Country as CountryService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=DeliveryAddress::class)
 */
final class DeliveryAddressRelations
{
    /** @var CountryService */
    private $countryService;

    public function __construct(
        CountryService $countryService
    ) {
        $this->countryService = $countryService;
    }

    /**
     * @Field()
     */
    public function country(DeliveryAddress $deliveryAddress): Country
    {
        return $this->countryService->country(
            (string) $deliveryAddress->countryId()
        );
    }
}
