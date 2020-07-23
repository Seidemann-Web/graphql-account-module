<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Service;

use OxidEsales\GraphQL\Account\Rating\DataType\Rating;
use OxidEsales\GraphQL\Catalogue\Product\Service\Product as ProductService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;

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
}
