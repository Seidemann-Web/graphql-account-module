<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Infrastructure;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Basket
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    public function addProduct(BasketDataType $basket, string $productId, float $amount): bool
    {
        $model = $basket->getEshopModel();
        $model->addItemToBasket($productId, $amount);

        return $this->repository->saveModel($model);
    }

    public function makePublic(BasketDataType $basket): bool
    {
        $model = $basket->getEshopModel();
        $model->assign([
            'oxuserbaskets__oxpublic' => 1,
        ]);

        return $this->repository->saveModel($model);
    }

    public function makePrivate(BasketDataType $basket): bool
    {
        $model = $basket->getEshopModel();
        $model->assign([
            'oxuserbaskets__oxpublic' => 0,
        ]);

        return $this->repository->saveModel($model);
    }
}
