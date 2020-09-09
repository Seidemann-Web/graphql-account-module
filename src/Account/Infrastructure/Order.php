<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use Iterator;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Application\Model\Voucher as EshopVoucherModel;
use OxidEsales\Eshop\Application\Model\VoucherList as EshopVoucherListModel;
use OxidEsales\GraphQL\Account\Account\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Account\DataType\OrderDelivery as OrderDeliveryDataType;
use OxidEsales\GraphQL\Account\Account\DataType\OrderDeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderInvoiceAddress;
use OxidEsales\GraphQL\Account\Account\DataType\OrderItem;
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

    public function delivery(OrderDataType $order): OrderDeliveryDataType
    {
        return new OrderDeliveryDataType($order->getEshopModel());
    }

    /**
     * @return Voucher[]
     */
    public function getOrderVouchers(OrderDataType $order): array
    {
        /** @var EshopVoucherListModel $list */
        $list = oxNew(EshopVoucherListModel::class);
        $list->selectString(
            'select * from oxvouchers where oxorderid = :orderId',
            ['orderId' => $order->getId()]
        );

        /** @var EshopVoucherModel[] $voucherModels */
        $voucherModels = $list->getArray();

        $usedVouchers = [];

        foreach ($voucherModels as $oneVoucher) {
            $usedVouchers[] = new Voucher($oneVoucher);
        }

        return $usedVouchers;
    }

    public function getOrderItems(OrderDataType $order): array
    {
        /** @var Iterator<OrderArticle> $orderArticles */
        $orderArticles = $order->getEshopModel()->getOrderArticles();
        $items         = [];

        foreach ($orderArticles as $oneArticle) {
            $items[] = new OrderItem($oneArticle);
        }

        return $items;
    }
}
