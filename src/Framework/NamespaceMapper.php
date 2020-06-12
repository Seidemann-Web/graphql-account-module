<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Framework;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;

class NamespaceMapper implements NamespaceMapperInterface
{
    public function getControllerNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Account\\Rating\\Controller' => __DIR__ . '/../Rating/Controller/',
            '\\OxidEsales\\GraphQL\\Account\\WishedPrice\\Controller' => __DIR__ . '/../WishedPrice/Controller/'
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Account\\Rating\\DataType' => __DIR__ . '/../Rating/DataType/',
            '\\OxidEsales\\GraphQL\\Account\\Rating\\Service' => __DIR__ . '/../Rating/Service/',
            '\\OxidEsales\\GraphQL\\Account\\WishedPrice\\DataType' => __DIR__ . '/../WishedPrice/DataType/',
        ];
    }
}
