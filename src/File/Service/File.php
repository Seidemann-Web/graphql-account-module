<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\File\Service;

use OxidEsales\GraphQL\Account\File\DataType\File as FileDataType;
use OxidEsales\GraphQL\Account\File\Exception\FileNotFound;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class File
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws FileNotFound
     */
    public function file(string $id): FileDataType
    {
        try {
            /** @var FileDataType $file */
            $file = $this->repository->getById(
                $id,
                FileDataType::class
            );
        } catch (NotFound $e) {
            throw FileNotFound::byId($id);
        }

        return $file;
    }
}
