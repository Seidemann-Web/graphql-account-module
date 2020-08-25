<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\DataType;

use OxidEsales\Eshop\Application\Model\Order as EshopOrderModel;
use OxidEsales\GraphQL\Catalogue\Product\Infrastructure\OrderProduct as OrderProductInfrastructure;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
final class OrderProductBruttoSum implements DataType
{
    /** @var EshopOrderModel */
    private $order;

    /** @var OrderProductInfrastructure */
    private $orderProductInfrastructure;

    public function __construct(
        EshopOrderModel $order,
        OrderProductInfrastructure $orderProductInfrastructure
    ) {
        $this->order                      = $order;
        $this->orderProductInfrastructure = $orderProductInfrastructure;
    }

    public function getEshopModel(): EshopOrderModel
    {
        return $this->order;
    }

    /**
     * @Field()
     */
    public function getSum(): float
    {
        return (float) ($this->order->getFieldData('oxtotalbrutsum'));
    }

    /**
     * @Field()
     */
    public function getVats(): array
    {
        return $this->orderProductInfrastructure->getVats($this->order);
    }

    public static function getModelClass(): string
    {
        return EshopOrderModel::class;
    }
}
