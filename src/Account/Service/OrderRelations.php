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
use OxidEsales\GraphQL\Account\Account\DataType\OrderDelivery;
use OxidEsales\GraphQL\Account\Account\DataType\OrderDeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderFile;
use OxidEsales\GraphQL\Account\Account\DataType\OrderInvoiceAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderItem;
use OxidEsales\GraphQL\Account\Account\DataType\Voucher;
use OxidEsales\GraphQL\Account\Account\Infrastructure\Order as OrderInfrastructure;
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
     * @Field()
     */
    public function delivery(OrderDataType $order): OrderDelivery
    {
        return $this->orderInfrastructure->delivery($order);
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

    /**
     * @Field
     *
     * @return OrderItem[]
     */
    public function getItems(OrderDataType $order): array
    {
        return $this->orderInfrastructure->getOrderItems($order);
    }

    /**
     * @Field
     *
     * @return OrderFile[]
     */
    public function getFiles(OrderDataType $order): array
    {
        return $this->orderInfrastructure->getOrderFiles($order);
    }
}
