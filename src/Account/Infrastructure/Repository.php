<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\Exception\CustomerNotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository as CatalogueRepository;

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
     * @throws CustomerNotFound
     */
    public function createUser(EshopUserModel $user): CustomerDataType
    {
        if (!$user->exists()) {
            $this->catalogueRepository->saveModel($user);
        }

        if (!$user->load($user->getId())) {
            throw CustomerNotFound::byEmail($user->getFieldData('oxemail'));
        }

        return new CustomerDataType($user);
    }
}
