<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\Eshop\Application\Model\VoucherList;
use OxidEsales\GraphQL\Account\Account\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Account\DataType\OrderDeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderInvoiceAddress;
use OxidEsales\GraphQL\Account\Account\DataType\Voucher;

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

    /**
     * @return Voucher[]
     */
    public function getOrderVouchers(OrderDataType $order): array
    {
        $list = oxNew(VoucherList::class);
        $list->selectString(
            'select * from oxvouchers where oxorderid = :orderId',
            ['orderId' => $order->getId()]
        );
        $voucherModels = $list->getArray();

        $usedVouchers = [];

        foreach ($voucherModels as $oneVoucher) {
            $usedVouchers[] = new Voucher($oneVoucher);
        }

        return $usedVouchers;
    }
}
