<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Shared\DataType;

use DateTimeImmutable;
use DateTimeInterface;
use OxidEsales\Eshop\Application\Model\UserBasketItem as EshopBasketItemModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class BasketItem implements DataType
{
    /** @var EshopBasketItemModel */
    private $basketItem;

    public function __construct(EshopBasketItemModel $basketItem)
    {
        $this->basketItem = $basketItem;
    }

    public function getEshopModel(): EshopBasketItemModel
    {
        return $this->basketItem;
    }

    /**
     * @Field()
     */
    public function getId(): ID
    {
        return new ID($this->basketItem->getId());
    }

    /**
     * @Field()
     */
    public function getAmount(): int
    {
        return (int) $this->basketItem->getFieldData('oxamount');
    }

    /**
     * @Field()
     */
    public function getLastUpdateDate(): DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->basketItem->getFieldData('oxtimestamp')
        );
    }

    public static function getModelClass(): string
    {
        return EshopBasketItemModel::class;
    }
}
