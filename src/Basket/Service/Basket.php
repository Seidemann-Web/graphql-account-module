<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Service;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Basket
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService
    ) {
        $this->repository              = $repository;
        $this->authenticationService   = $authenticationService;
    }

    /**
     * @throws BasketNotFound
     */
    public function basket(string $id): BasketDataType
    {
        try {
            /** @var BasketDataType $basket */
            $basket = $this->repository->getById(
                $id,
                BasketDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw BasketNotFound::byId($id);
        }

        if ($basket->public() === false && !$this->isSameUser($basket)) {
            throw new InvalidToken('Basket is private.');
        }

        return $basket;
    }

    private function isSameUser(BasketDataType $basket): bool
    {
        return (string) $basket->getUserId() === (string) $this->authenticationService->getUserId();
    }
}
