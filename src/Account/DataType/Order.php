<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\DataType;

use DateTimeImmutable;
use DateTimeInterface;
use OxidEsales\Eshop\Application\Model\Order as EshopOrderModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class Order implements DataType
{
    /** @var EshopOrderModel */
    private $order;

    public function __construct(EshopOrderModel $order)
    {
        $this->order = $order;
    }

    public function getEshopModel(): EshopOrderModel
    {
        return $this->order;
    }

    /**
     * @Field()
     */
    public function getId(): ID
    {
        return new ID($this->order->getId());
    }

    /**
     * @Field()
     */
    public function getOrderNumber(): int
    {
        return (int) ($this->order->getFieldData('oxordernr'));
    }

    /**
     * @Field()
     */
    public function getExported(): bool
    {
        return (bool) ($this->order->getFieldData('oxpixiexport'));
    }

    /**
     * @Field()
     */
    public function getInvoiceNumber(): int
    {
        return (int) ($this->order->getInvoiceNum());
    }

    /**
     * @Field()
     */
    public function getPaid(): ?DateTimeInterface
    {
        $paid = (string) $this->order->getFieldData('oxpaid');

        if ('0000-00-00 00:00:00' == $paid) {
            return null;
        }

        return new DateTimeImmutable($paid);
    }

    /**
     * @Field()
     */
    public function getRemark(): string
    {
        return (string) ($this->order->getFieldData('oxremark'));
    }

    /**
     * @Field()
     */
    public function getCancelled(): bool
    {
        return (bool) ($this->order->getFieldData('oxstorno'));
    }

    /**
     * @Field()
     */
    public function getInvoiced(): ?DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->order->getFieldData('oxbilldate')
        );
    }

    /**
     * @Field()
     */
    public function getOrdered(): ?DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->order->getFieldData('oxorderdate')
        );
    }

    /**
     * @Field()
     */
    public function getUpdated(): ?DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->order->getFieldData('oxtimestamp')
        );
    }

    /**
     * @Field
     *
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        $items         = [];
        $orderArticles = $this->order->getOrderArticles();

        foreach ($orderArticles as $oneArticle) {
            $items[] = new OrderItem($oneArticle);
        }

        return $items;
    }

    public static function getModelClass(): string
    {
        return EshopOrderModel::class;
    }
}
