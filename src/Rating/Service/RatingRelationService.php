<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Service;

use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Catalogue\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\DataType\Product;
use OxidEsales\GraphQL\Catalogue\Service\Product as ProductService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use OxidEsales\GraphQL\Account\Rating\DataType\Rating;

/**
 * @ExtendType(class=Rating::class)
 */
class RatingRelationService
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
                (string)$ratingModel->getFieldData('oxobjectid')
            );
        } catch (ProductNotFound | InvalidLogin $e) {
        }
        return null;
    }
}
