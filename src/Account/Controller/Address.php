<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\AddressFilterList;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
use OxidEsales\GraphQL\Account\Account\Service\Address as AddressService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

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

    /**
     * @Mutation()
     * @Logged()
     */
    public function customerDeliveryAddressDelete(string $id): bool
    {
        return $this->addressService->delete($id);
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

    /**
     * @Mutation()
     * @Logged
     */
    public function deliveryAddressAdd(DeliveryAddressDataType $deliveryAddress): DeliveryAddressDataType
    {
        return $deliveryAddress;
    }
}
