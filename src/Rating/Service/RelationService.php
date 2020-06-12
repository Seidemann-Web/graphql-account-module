<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Service;

use OxidEsales\GraphQL\Account\Rating\DataType\Rating;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Product;
use OxidEsales\GraphQL\Catalogue\Product\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Product\Service\Product as ProductService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=Rating::class)
 */
final class RelationService
{
    /** @var ProductService */
    private $productService;

    public function __construct(
        ProductService $productService
    ) {
        $this->productService = $productService;
    }

    /**
     * @Field()
     */
    public function product(Rating $rating): ?Product
    {
        $ratingModel = $rating->getEshopModel();

        if ($ratingModel->getFieldData('oxtype') !== 'oxarticle') {
            return null;
        }

        try {
            return $this->productService->product(
                (string) $ratingModel->getFieldData('oxobjectid')
            );
        } catch (ProductNotFound | InvalidLogin $e) {
        }

        return null;
    }
}
