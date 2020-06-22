<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\Service;

use OxidEsales\GraphQL\Account\WishedPrice\DataType\Inquirer as InquirerDataType;
use OxidEsales\GraphQL\Account\WishedPrice\Exception\InquirerNotFound;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Inquirer
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws InquirerNotFound
     */
    public function inquirer(string $id): InquirerDataType
    {
        try {
            /** @var InquirerDataType $inquirer */
            $inquirer = $this->repository->getById($id, InquirerDataType::class);
        } catch (NotFound $e) {
            throw InquirerNotFound::byId($id);
        }

        return $inquirer;
    }
}
