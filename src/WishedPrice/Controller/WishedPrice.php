<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\Controller;

use OxidEsales\GraphQL\Account\WishedPrice\DataType\WishedPrice as WishedPriceDataType;
use OxidEsales\GraphQL\Account\WishedPrice\DataType\WishedPriceRelationService;
use OxidEsales\GraphQL\Account\WishedPrice\Exception\WishedPriceNotFound;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Controller\Base;
use TheCodingMachine\GraphQLite\Annotations\Query;

class WishedPrice extends Base
{
    /**
     * @Query()
     *
     * @throws InvalidLogin
     * @throws WishedPriceNotFound
     * @throws InvalidToken
     */
    public function wishedPrice(string $id): WishedPriceDataType
    {
        /** Only logged in users can query wished price */
        if (!$this->isAuthenticated()) {
            throw new InvalidLogin('Unauthenticated');
        }

        try {
            /** @var WishedPriceDataType $wishedPrice */
            $wishedPrice = $this->repository->getById(
                $id,
                WishedPriceDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw WishedPriceNotFound::byId($id);
        }

        /** If the logged in user is authorized return the wished price */
        if ($this->isAuthorized('VIEW_WISHED_PRICES')) {
            return $wishedPrice;
        }

        /** A user can query only its own wished price */
        $wishedPriceRelationService = new WishedPriceRelationService($this->repository);
        $user = $wishedPriceRelationService->getUser($wishedPrice);
        if (!$user || $user->getUserName() !== $this->whoIsAuthenticated()) {
            throw new InvalidLogin('Unauthorized');
        }

        /** Check disable wished price flag */
        $product = $wishedPriceRelationService->getProduct($wishedPrice);
        if (!$product->wishedPriceEnabled()) {
            throw WishedPriceNotFound::byId($id);
        }

        return $wishedPrice;
    }
}
