<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\Eshop\Application\Model\Payment as EshopPaymentModel;
use OxidEsales\GraphQL\Account\Account\DataType\OrderPayment as OrderPaymentDataType;
use OxidEsales\GraphQL\Account\Account\DataType\OrderPaymentValue;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment;

final class OrderPayment
{
    public function getPayment(OrderPaymentDataType $orderPayment): ?Payment
    {
        $payment = $orderPayment->getEshopModel();

        if (empty($payment)) {
            return null;
        }

        /** @var EshopPaymentModel */
        $paymentModel = oxNew(EshopPaymentModel::class);
        $paymentModel->load($payment->getFieldData('oxpaymentsid'));

        return new Payment($paymentModel);
    }

    public function getPaymentValues(OrderPaymentDataType $orderPayment): array
    {
        $values  = [];
        $payment = $orderPayment->getEshopModel();

        if (empty($payment)) {
            return $values;
        }

        foreach ($payment->getDynValues() as $paymentValue) {
            $values[] = new OrderPaymentValue($paymentValue);
        }

        return $values;
    }
}
