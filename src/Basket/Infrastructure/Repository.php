<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Infrastructure;

use Doctrine\DBAL\FetchMode;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as CatalogueRepository;
use TheCodingMachine\GraphQLite\Types\ID;

final class Repository
{
    /** @var CatalogueRepository */
    private $catalogueRepository;

    public function __construct(
        CatalogueRepository $catalogueRepository
    ) {
        $this->catalogueRepository = $catalogueRepository;
    }

    /**
     * @throws BasketNotFound
     */
    public function getBasketById(string $id): BasketDataType
    {
        try {
            /** @var BasketDataType $basket */
            $basket = $this->catalogueRepository->getById(
                $id,
                BasketDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw BasketNotFound::byId($id);
        }

        return $basket;
    }

    /**
     * @throws BasketNotFound
     */
    public function getCustomerBasketByTitle(CustomerDataType $customer, string $title): BasketDataType
    {
        $basket = $customer->getEshopModel()->getBasket($title);

        if ($basket->isNewBasket()) {
            throw BasketNotFound::byTitle($title);
        }

        return $this->getBasketById($basket->getId());
    }

    /**
     * @throws BasketNotFound
     *
     * @return BasketDataType[]
     */
    public function getCustomerBaskets(CustomerDataType $customer): array
    {
        $baskets   = [];
        $basketIds = $this->getCustomerBasketIds($customer->getId());

        if (!is_array($basketIds)) {
            return $baskets;
        }

        foreach ($basketIds as $basketId) {
            $baskets[] = $this->getBasketById($basketId);
        }

        return $baskets;
    }

    private function getCustomerBasketIds(ID $customerId): ?array
    {
        $queryBuilder = ContainerFactory::getInstance()
        ->getContainer()
        ->get(QueryBuilderFactoryInterface::class)
        ->create();

        return $queryBuilder
            ->select('oxid')
            ->from('oxuserbaskets')
            ->where('oxuserid = :customerId')
            ->setParameter(':customerId', $customerId)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);
    }
}
