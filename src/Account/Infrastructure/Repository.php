<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use Doctrine\DBAL\FetchMode;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Account\Exception\CustomerNotFound;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Exception\BasketNotFound;
use OxidEsales\GraphQL\Account\Basket\Service\Basket as BasketService;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as CatalogueRepository;
use TheCodingMachine\GraphQLite\Types\ID;

final class Repository
{
    /** @var CatalogueRepository */
    private $catalogueRepository;

    /** @var BasketService */
    private $basketService;

    public function __construct(
        CatalogueRepository $catalogueRepository,
        BasketService $basketService
    ) {
        $this->catalogueRepository = $catalogueRepository;
        $this->basketService       = $basketService;
    }

    /**
     * @throws CustomerNotFound
     */
    public function createUser(EshopUserModel $user): CustomerDataType
    {
        if (!$user->exists()) {
            $this->catalogueRepository->saveModel($user);
        }

        if (!$user->load($user->getId())) {
            throw CustomerNotFound::byId($user->getId());
        }

        return new CustomerDataType($user);
    }

    /**
     * @return DeliveryAddress[]
     */
    public function addresses(CustomerDataType $customer): array
    {
        $addresses   = [];
        $addressList = $customer->getEshopModel()
                                ->getUserAddresses();

        foreach ($addressList as $address) {
            $addresses[] = new DeliveryAddress($address);
        }

        return $addresses;
    }

    public function checkEmailExists(string $email): bool
    {
        /** @var EshopUserModel $customerModel */
        $customerModel = oxNew(CustomerDataType::getModelClass());

        return (bool) $customerModel->checkIfEmailExists($email);
    }

    public function basket(CustomerDataType $customer, string $title): BasketDataType
    {
        $basket = $customer->getEshopModel()->getBasket($title);

        return $this->basketService->basket($basket->getId());
    }

    /**
     * @throws BasketNotFound
     *
     * @return BasketDataType[]
     */
    public function baskets(CustomerDataType $customer): array
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
