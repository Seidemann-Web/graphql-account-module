<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\DataType;

use DateTimeImmutable;
use DateTimeInterface;
use OxidEsales\Eshop\Application\Model\UserBasket as WishListModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class WishList implements DataType
{
    /** @var WishListModel */
    private $wishList;

    public function __construct(
        WishListModel $wishList
    ) {
        $this->wishList = $wishList;
    }

    public function getEshopModel(): WishListModel
    {
        return $this->wishList;
    }

    /**
     * @Field()
     */
    public function getId(): ID
    {
        return new ID(
            $this->wishList->getId()
        );
    }

    /**
     * @Field()
     */
    public function getPublic(): bool
    {
        return (bool) $this->wishList->getFieldData('oxpublic');
    }

    public function setPublic(bool $public): void
    {
        $value = $public ? 1 : 0;

        $this->wishList->assign(['oxpublic' => $value]);
        $this->wishList->save();
    }

    /**
     * @Field()
     */
    public function getCreationDate(): DateTimeInterface
    {
        return new DateTimeImmutable((string) $this->wishList->getFieldData('oxtimestamp'));
    }

    /**
     * @Field()
     */
    public function getLastUpdateDate(): DateTimeInterface
    {
        return new DateTimeImmutable((string) $this->wishList->getFieldData('oxupdate'));
    }

    /**
     * @return class-string
     */
    public static function getModelClass(): string
    {
        return WishListModel::class;
    }
}
