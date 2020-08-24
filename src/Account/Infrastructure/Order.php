<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\GraphQL\Account\Account\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Account\DataType\OrderDeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderInvoiceAddress;

final class Order
{
    public function deliveryAddress(OrderDataType $order): ?OrderDeliveryAddress
    {
        $result    = new OrderDeliveryAddress($order->getEshopModel());
        $countryId = (string) $result->countryId();

        if (empty($countryId)) {
            $result = null;
        }

        return $result;
    }

    public function invoiceAddress(OrderDataType $order): OrderInvoiceAddress
    {
        return new OrderInvoiceAddress($order->getEshopModel());
    }

    public function getOrderCurrencyName(OrderDataType $order): string
    {
        return (string) $order->getEshopModel()->getFieldData('oxcurrency');
    }
}
