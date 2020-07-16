<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Infrastructure;

use Doctrine\DBAL\FetchMode;
use OxidEsales\Eshop\Application\Model\UserBasket as UserBasketEshopModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as SharedRepository;
use PDO;
use TheCodingMachine\GraphQLite\Types\ID;
use function getViewName;

final class Repository
{
    /** @var SharedRepository */
    private $sharedRepository;

    /** @var QueryBuilderFactoryInterface */
    private $queryBuilderFactory;

    public function __construct(
        SharedRepository $sharedRepository,
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->sharedRepository    = $sharedRepository;
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @throws BasketNotFound
     */
    public function customerBasketByTitle(CustomerDataType $customer, string $title): BasketDataType
    {
        $model = $customer->getEshopModel()->getBasket($title);

        if (!$model->getId()) {
            throw BasketNotFound::byOwnerAndTitle(
                (string) $customer->getId(),
                $title
            );
        }

        return new BasketDataType($model);
    }

    /**
     * @throws BasketNotFound
     *
     * @return BasketDataType[]
     */
    public function customerBaskets(CustomerDataType $customer): array
    {
        $baskets   = [];
        $basketIds = $this->getCustomerBasketIds($customer->getId());

        if (!is_array($basketIds)) {
            return $baskets;
        }

        foreach ($basketIds as $basketId) {
            $baskets[] = $this->sharedRepository->getById(
                $basketId,
                BasketDataType::class,
                false
            );
        }

        return $baskets;
    }

    /**
     * @return BasketDataType[]
     */
    public function publicBasketsByOwnerNameOrEmail(string $search): array
    {
        $baskets = [];

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->select('userbaskets.*')
                     ->from(getViewName('oxuserbaskets'), 'userbaskets')
                     ->innerJoin('userbaskets', getViewName('oxuser'), 'users', 'users.oxid = userbaskets.oxuserid')
                     ->where('userbaskets.oxpublic = 1')
                     ->andWhere('(users.oxusername = :search OR users.oxlname = :search)')
                     ->setParameters([
                         ':search' => $search,
                     ]);

        $queryBuilder->getConnection()->setFetchMode(PDO::FETCH_ASSOC);
        /** @var \Doctrine\DBAL\Statement $result */
        $result = $queryBuilder->execute();

        /** @var UserBasketEshopModel */
        $model = oxNew(UserBasketEshopModel::class);

        foreach ($result as $row) {
            $newModel = clone $model;
            $newModel->assign($row);
            $baskets[] = new BasketDataType($newModel);
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
