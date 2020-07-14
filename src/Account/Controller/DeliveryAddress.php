<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class DeliveryAddress
{
    /**
     * @Mutation()
     */
    public function deliveryAddressAdd(DeliveryAddressDataType $deliveryAddress): DeliveryAddressDataType
    {
        return $deliveryAddress;
    }
}
