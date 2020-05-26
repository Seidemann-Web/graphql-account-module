<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\Service;

use OxidEsales\GraphQL\Account\WishedPrice\DataType\WishedPrice;
use OxidEsales\GraphQL\Account\WishedPrice\Exception\WishedPriceNotFound;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Service\Repository;

class WishedPriceService
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param string $wishedPriceId
     * @return WishedPrice
     * @throws InvalidLogin
     * @throws WishedPriceNotFound
     */
    public function delete(string $wishedPriceId): WishedPrice
    {
        try {
            /** @var WishedPrice $wishedPrice */
            $wishedPrice = $this->repository->getById($wishedPriceId, WishedPrice::class);
        } catch (NotFound $e) {
            throw WishedPriceNotFound::byId($wishedPriceId);
        }

        if (
            !$this->authenticationService->isLogged() ||
            !$this->authorizationService->isAllowed('DELETE_WISHED_PRICE')
        ) {
            throw new InvalidLogin("Unauthorized");
        }

        $this->repository->delete($wishedPriceId, WishedPrice::class);

        return $wishedPrice;
    }
}
