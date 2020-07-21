<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Service;

use OxidEsales\Eshop\Application\Model\UserBasket as BasketModel;
use OxidEsales\GraphQL\Account\Account\Service\Customer as CustomerService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketExists;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Account\Basket\Infrastructure\Repository as BasketRepository;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as CatalogueRepository;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class BasketInput
{
    /** @var Authentication */
    private $authentication;

    /** @var BasketRepository */
    private $basketRepository;

    /** @var CatalogueRepository */
    private $repository;

    /** @var CustomerService */
    private $customerService;

    public function __construct(
        Authentication $authentication,
        CatalogueRepository $repository,
        BasketRepository $basketRepository,
        CustomerService $customerService
    ) {
        $this->authentication   = $authentication;
        $this->repository       = $repository;
        $this->basketRepository = $basketRepository;
        $this->customerService  = $customerService;
    }

    /**
     * @Factory()
     */
    public function fromUserInput(string $title, bool $public = true): BasketDataType
    {
        if ($this->doesBasketExist($title)) {
            throw BasketExists::byTitle($title);
        }

        /** @var BasketModel */
        $model = oxNew(BasketModel::class);
        $model->assign([
            'OXUSERID' => $this->authentication->getUserId(),
            'OXTITLE'  => $title,
            'OXPUBLIC' => $public,
        ]);

        return new BasketDataType($model);
    }

    private function doesBasketExist(string $title): bool
    {
        $customer = $this->customerService->customer($this->authentication->getUserId());

        try {
            $this->basketRepository->getCustomerBasketByTitle($customer, $title);
        } catch (BasketNotFound $e) {
            return false;
        }

        return true;
    }
}
