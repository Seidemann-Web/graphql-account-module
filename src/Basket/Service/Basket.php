<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Service;

use OxidEsales\GraphQL\Account\Account\Exception\CustomerNotFound;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\DataType\BasketOwner as BasketOwnerDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Account\Basket\Infrastructure\Repository as BasketRepository;
use OxidEsales\GraphQL\Account\Basket\Infrastructure\Basket as BasketInfraService;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Base\Service\Legacy;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Basket
{
    /** @var Repository */
    private $repository;

    /** @var BasketRepository */
    private $basketRepository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Authorization */
    private $authorizationService;

    /** @var Legacy */
    private $legacyService;

    /** @var BasketInfraService */
    private $basketInfraService;

    public function __construct(
        Repository $repository,
        BasketRepository $basketRepository,
        Authentication $authenticationService,
        Authorization $authorizationService,
        Legacy $legacyService,
        BasketInfraService $basketInfraService
    ) {
        $this->repository            = $repository;
        $this->basketRepository      = $basketRepository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService  = $authorizationService;
        $this->legacyService         = $legacyService;
        $this->basketInfraService    = $basketInfraService;
    }

    /**
     * @throws BasketNotFound
     * @throws InvalidToken
     */
    public function basket(string $id): BasketDataType
    {
        $basket = $this->basketRepository->getBasketById($id);

        if ($basket->public() === false && !$this->isSameUser($basket)) {
            throw new InvalidToken('Basket is private.');
        }

        return $basket;
    }

    /**
     * @throws BasketNotFound
     * @throws InvalidToken
     */
    public function remove(string $id): bool
    {
        $basket = $this->basketRepository->getBasketById($id);

        //user can remove only his own baskets unless otherwise authorized
        if (
            $this->authorizationService->isAllowed('DELETE_BASKET')
            || $this->isSameUser($basket)
        ) {
            return $this->repository->delete($basket->getEshopModel());
        }

        throw new InvalidLogin('Unauthorized');
    }

    /**
     * @throws CustomerNotFound
     */
    public function basketOwner(string $id): BasketOwnerDataType
    {
        $ignoreSubShop = (bool) $this->legacyService->getConfigParam('blMallUsers');

        try {
            /** @var BasketOwnerDataType $customer */
            $customer = $this->repository->getById(
                $id,
                BasketOwnerDataType::class,
                $ignoreSubShop
            );
        } catch (NotFound $e) {
            throw CustomerNotFound::byId($id);
        }

        return $customer;
    }

    public function addProduct(string $basketId, string $productId, float $amount): BasketDataType
    {
        $basket = $this->basketRepository->getBasketById($basketId);

        if (!$this->isSameUser($basket)) {
            throw new InvalidLogin('Unauthorized');
        }

        $this->basketInfraService->addProduct($basket, $productId, $amount);

        return $basket;
    }

    /**
     * @throws InvalidLogin
     * @throws InvalidToken
     */
    public function store(BasketDataType $basket): bool
    {
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        return $this->repository->saveModel($basket->getEshopModel());
    }

    private function isSameUser(BasketDataType $basket): bool
    {
        return (string) $basket->getUserId() === (string) $this->authenticationService->getUserId();
    }
}
