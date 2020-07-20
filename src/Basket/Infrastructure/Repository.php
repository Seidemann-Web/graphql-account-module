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
use OxidEsales\GraphQL\Account\Basket\Service\Basket as BasketService;
use TheCodingMachine\GraphQLite\Types\ID;

final class Repository
{
    /** @var BasketService */
    private $basketService;

    public function __construct(
        BasketService $basketService
    ) {
        $this->basketService       = $basketService;
    }

    public function getCustomerBasketByTitle(CustomerDataType $customer, string $title): BasketDataType
    {
        $basket = $customer->getEshopModel()->getBasket($title);

        return $this->basketService->basket($basket->getId());
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
            $baskets[] = $this->basketService->basket($basketId);
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
