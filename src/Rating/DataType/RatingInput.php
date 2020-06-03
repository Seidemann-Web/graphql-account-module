<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\DataType;

use OxidEsales\GraphQL\Account\Rating\Exception\RatingOutOfBounds;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\DataType\Product;
use OxidEsales\GraphQL\Catalogue\DataType\Rating;
use OxidEsales\GraphQL\Catalogue\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Service\Repository;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class RatingInput
{
    private $authentication;
    private $repository;

    public function __construct(
        Authentication $authentication,
        Repository $repository
    ) {
        $this->authentication = $authentication;
        $this->repository = $repository;
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $productId, int $rating): Rating
    {
        $this->assertRatingValue($rating);
        $this->assertProductIdValue($productId);

        $model = oxNew(\OxidEsales\Eshop\Application\Model\Rating::class);
        $model->assign([
            'OXTYPE' => 'oxarticle',
            'OXOBJECTID' => $productId,
            'OXRATING' => $rating,
            'OXUSERID' => $this->authentication->getUserId()
        ]);

        return new Rating($model);
    }

    private function assertRatingValue($rating)
    {
        if ($rating < 1 || $rating > 5) {
            throw new RatingOutOfBounds();
        }
    }

    private function assertProductIdValue($productId)
    {
        try {
            /** @var Product $product */
            $product = $this->repository->getById($productId, Product::class);
        } catch (NotFound $e) {
            throw ProductNotFound::byId($productId);
        }
    }
}
