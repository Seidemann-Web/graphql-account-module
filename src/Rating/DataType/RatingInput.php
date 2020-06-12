<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\DataType;

use OxidEsales\Eshop\Application\Model\Rating as RatingEshopModel;
use OxidEsales\GraphQL\Account\Rating\Exception\RatingOutOfBounds;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Product;
use OxidEsales\GraphQL\Catalogue\Product\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class RatingInput
{
    /** @var Authentication */
    private $authentication;

    /** @var Repository */
    private $repository;

    public function __construct(
        Authentication $authentication,
        Repository $repository
    ) {
        $this->authentication = $authentication;
        $this->repository     = $repository;
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $productId, int $rating): Rating
    {
        $this->assertRatingValue($rating);
        $this->assertProductIdValue($productId);

        /** @var RatingEshopModel */
        $model = oxNew(RatingEshopModel::class);
        $model->assign([
            'OXTYPE'     => 'oxarticle',
            'OXOBJECTID' => $productId,
            'OXRATING'   => $rating,
            'OXUSERID'   => $this->authentication->getUserId(),
        ]);

        return new Rating($model);
    }

    /**
     * @throws RatingOutOfBounds
     *
     * @return true
     */
    private function assertRatingValue(int $rating): bool
    {
        if ($rating < 1 || $rating > 5) {
            throw RatingOutOfBounds::byWrongValue($rating);
        }

        return true;
    }

    /**
     * @throws ProductNotFound
     *
     * @return true
     */
    private function assertProductIdValue(string $productId): bool
    {
        try {
            /** @var Product $product */
            $product = $this->repository->getById($productId, Product::class);
        } catch (NotFound $e) {
            throw ProductNotFound::byId($productId);
        }

        return true;
    }
}
