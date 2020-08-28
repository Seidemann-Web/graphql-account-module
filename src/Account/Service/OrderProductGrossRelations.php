<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\OrderProductBruttoSum;
use OxidEsales\GraphQL\Account\Account\DataType\OrderProductVats;
use OxidEsales\GraphQL\Account\Account\Infrastructure\OrderProduct as OrderProductInfrastructure;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=OrderProductBruttoSum::class)
 */
final class OrderProductGrossRelations
{
    /** @var OrderProductInfrastructure */
    private $orderProductInfrastructure;

    public function __construct(
        OrderProductInfrastructure $orderProductInfrastructure
    ) {
        $this->orderProductInfrastructure = $orderProductInfrastructure;
    }

    /**
     * @Field()
     *
     * @return OrderProductVats[]
     */
    public function getVats(OrderProductBruttoSum $orderProductGross): array
    {
        return $this->orderProductInfrastructure->getVats($orderProductGross);
    }
}
