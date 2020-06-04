<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\DataType;

use OxidEsales\Eshop\Core\Price as EshopPriceModel;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\DataType\Currency;
use OxidEsales\GraphQL\Catalogue\DataType\Price;
use OxidEsales\GraphQL\Catalogue\DataType\Product;
use OxidEsales\GraphQL\Catalogue\DataType\User;
use OxidEsales\GraphQL\Catalogue\Service\Repository;
use stdClass;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use OxidEsales\GraphQL\Catalogue\Service\Product as CatalogueProductService;

/**
 * @ExtendType(class=WishedPrice::class)
 */
class WishedPriceRelationService
{
    /** @var Repository */
    private $repository;

    /** @var CatalogueProductService */
    private $productService;

    public function __construct(
        Repository $repository,
        CatalogueProductService $productService
    ) {
        $this->repository = $repository;
        $this->productService = $productService;
    }

    /**
     * @Field()
     */
    public function getUser(WishedPrice $wishedPrice): ?User
    {
        $user = null;

        try {
            if ($userId = (string)$wishedPrice->getUserId()) {
                $user = $this->repository->getById(
                    $userId,
                    User::class,
                    false
                );
            }
        } catch (NotFound $e) {
            return null;
        }

        return $user;
    }

    /**
     * @Field()
     */
    public function getProduct(WishedPrice $wishedPrice): Product
    {
        /** @var Product $product */
        $product = $this->productService->product(
            (string)$wishedPrice->getProductId()
        );

        return $product;
    }

    /**
     * @Field()
     */
    public function getPrice(WishedPrice $wishedPrice): Price
    {
        /** @var EshopPriceModel $price */
        $price = oxNew(EshopPriceModel::class);
        $price->setPrice((double)$wishedPrice->getEshopModel()->getFieldData('oxprice'));

        return new Price($price);
    }

    /**
     * @Field()
     */
    public function getCurrency(WishedPrice $wishedPrice): Currency
    {
        /** @var stdClass $currency */
        $currency = $wishedPrice->getEshopModel()->getPriceAlarmCurrency();

        return new Currency($currency);
    }
}
