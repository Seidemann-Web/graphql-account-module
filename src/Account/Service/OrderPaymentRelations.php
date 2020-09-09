<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\OrderPayment;
use OxidEsales\GraphQL\Account\Account\DataType\OrderPaymentValue;
use OxidEsales\GraphQL\Account\Account\Infrastructure\OrderPayment as OrderPaymentInfrastructure;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=OrderPayment::class)
 */
final class OrderPaymentRelations
{
    /** @var OrderPaymentInfrastructure */
    private $orderPaymentInfrastructure;

    public function __construct(
        OrderPaymentInfrastructure $orderPaymentInfrastructure
    ) {
        $this->orderPaymentInfrastructure = $orderPaymentInfrastructure;
    }

    /**
     * @Field()
     */
    public function getPayment(OrderPayment $orderPayment): ?Payment
    {
        return $this->orderPaymentInfrastructure->getPayment($orderPayment);
    }

    /**
     * @Field()
     *
     * @return OrderPaymentValue[]
     */
    public function getValues(OrderPayment $orderPayment): array
    {
        return $this->orderPaymentInfrastructure->getPaymentValues($orderPayment);
    }
}
