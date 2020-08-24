<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\Eshop\Application\Model\Order as EshopOrderModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Core\Model\ListModel as EshopListModel;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Base\DataType\PaginationFilter;

final class Customer
{
    /**
     * @return OrderDataType[]
     */
    public function getOrders(CustomerDataType $customer, ?PaginationFilter $pagination = null): array
    {
        $limit = false;
        $page  = 0;

        if ($pagination) {
            $limit = 0 < (int) $pagination->limit() ? (int) $pagination->limit() : $limit;

            if ($limit) {
                $offset = (int) $pagination->offset();
                $page   = (int) $offset / $limit;
            }
        }

        /** @var EshopUserModel $customerModel */
        $customerModel = $customer->getEshopModel();

        if ($limit) {
            /** @var EshopListModel $ordersList */
            $ordersList = $customerModel->getOrders($limit, $page);
        } else {
            /** @var EshopListModel $ordersList */
            $ordersList = $customerModel->getOrders();
        }

        $orders     = [];

        foreach ($ordersList->getArray() as $orderId => $orderModel) {
            /** @var EshopOrderModel @$orderModel */
            $orders[] = new OrderDataType($orderModel);
        }

        return $orders;
    }
}
