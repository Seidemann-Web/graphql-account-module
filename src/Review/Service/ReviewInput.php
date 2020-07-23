<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Review\Service;

use OxidEsales\Eshop\Application\Model\Review as ReviewEshopModel;
use OxidEsales\GraphQL\Account\Review\Exception\RatingOutOfBounds;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Product;
use OxidEsales\GraphQL\Catalogue\Product\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Review\DataType\Review;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class ReviewInput
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
    public function fromUserInput(string $productId, string $text, int $rating): Review
    {
        $this->assertRatingValue($rating);
        $this->assertProductIdValue($productId);

        /** @var ReviewEshopModel */
        $model = oxNew(ReviewEshopModel::class);
        $model->assign([
            'OXTYPE'     => 'oxarticle',
            'OXOBJECTID' => $productId,
            'OXRATING'   => $rating,
            'OXUSERID'   => $this->authentication->getUserId(),
            'OXTEXT'     => $text,
        ]);

        return new Review($model);
    }

    /**
     * @throws RatingOutOfBounds
     *
     * @return true
     */
    private function assertRatingValue(int $review): bool
    {
        if ($review < 1 || $review > 5) {
            throw RatingOutOfBounds::byWrongValue($review);
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
            $this->repository->getById($productId, Product::class);
        } catch (NotFound $e) {
            throw ProductNotFound::byId($productId);
        }

        return true;
    }
}
