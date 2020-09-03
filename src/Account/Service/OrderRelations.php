<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Account\DataType\OrderCost;
use OxidEsales\GraphQL\Account\Account\DataType\OrderDeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderInvoiceAddress;
use OxidEsales\GraphQL\Account\Account\DataType\Voucher;
use OxidEsales\GraphQL\Account\Account\Infrastructure\Order as OrderInfrastructure;
use OxidEsales\GraphQL\Catalogue\Currency\DataType\Currency;
use OxidEsales\GraphQL\Catalogue\Currency\Infrastructure\Repository as CurrencyRepository;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=OrderDataType::class)
 */
final class OrderRelations
{
    /** @var OrderInfrastructure */
    private $orderInfrastructure;

    /** @var CurrencyRepository */
    private $currencyRepository;

    public function __construct(
        OrderInfrastructure $orderInfrastructure,
        CurrencyRepository $currencyRepository
    ) {
        $this->orderInfrastructure = $orderInfrastructure;
        $this->currencyRepository  = $currencyRepository;
    }

    /**
     * @Field()
     */
    public function currency(OrderDataType $order): Currency
    {
        return $this->currencyRepository->getByName(
            $this->orderInfrastructure->getOrderCurrencyName($order)
        );
    }

    /**
     * @Field()
     */
    public function invoiceAddress(OrderDataType $order): OrderInvoiceAddress
    {
        return $this->orderInfrastructure->invoiceAddress($order);
    }

    /**
     * @Field()
     */
    public function deliveryAddress(OrderDataType $order): ?OrderDeliveryAddress
    {
        return $this->orderInfrastructure->deliveryAddress($order);
    }

    /**
     * @Field()
     */
    public function cost(OrderDataType $order): OrderCost
    {
        return new OrderCost($order->getEshopModel());
    }

    /**
     * @Field
     *
     * @return Voucher[]
     */
    public function vouchers(OrderDataType $order): array
    {
        return $this->orderInfrastructure->getOrderVouchers($order);
    }
}
