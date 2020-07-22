<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\DataType;

use DateTimeImmutable;
use DateTimeInterface;
use OxidEsales\Eshop\Application\Model\UserBasket as BasketModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class Basket implements DataType
{
    /** @var BasketModel */
    private $basket;

    public function __construct(
        BasketModel $basket
    ) {
        $this->basket = $basket;
    }

    public function getEshopModel(): BasketModel
    {
        return $this->basket;
    }

    /**
     * @Field()
     */
    public function id(): ID
    {
        return new ID(
            $this->basket->getId()
        );
    }

    /**
     * @Field()
     * Beware of the following values with special meaning
     * - wishList
     * - noticeList
     * - savedBasket
     */
    public function title(): string
    {
        return (string) $this->basket->getFieldData('oxtitle');
    }

    /**
     * @Field()
     */
    public function public(): bool
    {
        return (bool) $this->basket->getFieldData('oxpublic');
    }

    public function setPublic(bool $public): void
    {
        $value = $public ? 1 : 0;

        $this->basket->assign(['oxpublic' => $value]);
        $this->basket->save();
    }

    /**
     * @Field()
     */
    public function creationDate(): DateTimeInterface
    {
        return new DateTimeImmutable((string) $this->basket->getFieldData('oxtimestamp'));
    }

    /**
     * @Field()
     */
    public function lastUpdateDate(): ?DateTimeInterface
    {
        $timeStamp = (int) $this->basket->getFieldData('oxupdate');

        if ($timeStamp > 0) {
            return (new DateTimeImmutable())->setTimestamp($timeStamp);
        }

        return null;
    }

    public function getUserId(): ID
    {
        return new ID(
            (string) $this->basket->getFieldData('oxuserid')
        );
    }

    public function belongsToUser(string $userId): bool
    {
        return (string) $this->getUserId() === $userId;
    }

    /**
     * @return class-string
     */
    public static function getModelClass(): string
    {
        return BasketModel::class;
    }
}
