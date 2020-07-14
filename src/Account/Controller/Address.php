<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\AddressFilterList;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Account\Service\Address as AddressService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Address
{
    /** @var AddressService */
    private $addressService;

    public function __construct(
        AddressService $addressService
    ) {
        $this->addressService = $addressService;
    }

    /**
     * @Query()
     * @Logged()
     *
     * @return DeliveryAddressDataType[]
     */
    public function customerDeliveryAddresses(): array
    {
        return $this->addressService->customerDeliveryAddresses(
            new AddressFilterList()
        );
    }
}
