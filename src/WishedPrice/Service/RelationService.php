<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\Service;

use OxidEsales\Eshop\Core\Price as EshopPriceModel;
use OxidEsales\GraphQL\Account\WishedPrice\DataType\Inquirer as InquirerDataType;
use OxidEsales\GraphQL\Account\WishedPrice\DataType\WishedPrice;
use OxidEsales\GraphQL\Account\WishedPrice\Exception\InquirerNotFound;
use OxidEsales\GraphQL\Account\WishedPrice\Service\Inquirer as InquirerService;
use OxidEsales\GraphQL\Catalogue\Currency\DataType\Currency;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Price;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Product;
use OxidEsales\GraphQL\Catalogue\Product\Service\Product as CatalogueProductService;
use stdClass;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=WishedPrice::class)
 */
final class RelationService
{
    /** @var InquirerService */
    private $inquirerService;

    /** @var CatalogueProductService */
    private $productService;

    public function __construct(
        InquirerService $inquirerService,
        CatalogueProductService $productService
    ) {
        $this->inquirerService    = $inquirerService;
        $this->productService     = $productService;
    }

    /**
     * @Field()
     */
    public function getInquirer(WishedPrice $wishedPrice): ?InquirerDataType
    {
        try {
            return $this->inquirerService->inquirer((string) $wishedPrice->getInquirerId());
        } catch (InquirerNotFound $e) {
        }

        return null;
    }

    /**
     * @Field()
     */
    public function getProduct(WishedPrice $wishedPrice): Product
    {
        return $this->productService->product(
            (string) $wishedPrice->getProductId()
        );
    }

    /**
     * @Field()
     */
    public function getPrice(WishedPrice $wishedPrice): Price
    {
        /** @var EshopPriceModel $price */
        $price = oxNew(EshopPriceModel::class);
        $price->setPrice((float) $wishedPrice->getEshopModel()->getFieldData('oxprice'));

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
