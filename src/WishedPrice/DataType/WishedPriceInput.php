<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\DataType;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\PriceAlarm;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\DataType\Product as ProductDataType;
use OxidEsales\GraphQL\Catalogue\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Service\CurrencyRepository;
use OxidEsales\GraphQL\Catalogue\Service\Repository;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

class WishedPriceInput
{
    /** @var Authentication */
    private $authentication;

    /** @var Repository */
    private $repository;

    /** @var CurrencyRepository */
    private $currencyRepository;

    public function __construct(
        Authentication $authentication,
        Repository $repository,
        CurrencyRepository $currencyRepository
    ) {
        $this->authentication = $authentication;
        $this->repository = $repository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @Factory
     */
    public function fromUserInput(ID $productId, string $currencyName, float $price): WishedPrice
    {
        $this->assertProductWishedPriceIsPossible($productId);

        $currency = $this->currencyRepository->getByName($currencyName);

        /** @var PriceAlarm $model */
        $model = oxNew(PriceAlarm::class);
        $model->assign(
            [
                'OXUSERID'   => $this->authentication->getUserId(),
                'OXEMAIL'    => $this->authentication->getUserName(),
                'OXARTID'    => (string)$productId->val(),
                'OXPRICE'    => round($price, $currency->getPrecision()),
                'OXCURRENCY' => $currency->getName()
            ]
        );

        return new WishedPrice($model);
    }

    /**
     * @throws ProductNotFound
     * @return true
     */
    private function assertProductWishedPriceIsPossible(ID $productId): bool
    {
        $id = (string)$productId->val();
        try {
            /** @var ProductDataType $product */
            $product = $this->repository->getById($id, ProductDataType::class);
        } catch (NotFound $e) {
            throw ProductNotFound::byId($id);
        }

        // Throw 404 if product has wished prices disabled
        if (!$product->getEshopModel()->isPriceAlarm()) {
            throw ProductNotFound::byId($id);
        }

        return true;
    }
}
