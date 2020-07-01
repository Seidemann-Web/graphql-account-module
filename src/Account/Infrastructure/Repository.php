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
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusSubscribe;
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
    public function createNewsletterUser(NewsletterStatusSubscribe $input): CustomerDataType
    {
        /** @var EshopUserModel $user */
        $user = oxNew(EshopUserModel::class);

        $user->assign(
            [
                'oxactive'   => 1,
                'oxrights'   => 'user',
                'oxsal'      => $input->salutation(),
                'oxfname'    => $input->firstname(),
                'oxlname'    => $input->lastname(),
                'oxusername' => $input->email(),
            ]
        );

        if (!$user->exists()) {
            $this->catalogueRepository->saveModel($user);
        }

        if (!$user->load($user->getId())) {
            throw CustomerNotFound::byEmail($input->email());
        }

        return new CustomerDataType($user);
    }
}
