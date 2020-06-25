<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Shared\Service;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;

final class NamespaceMapper implements NamespaceMapperInterface
{
    public function getControllerNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Account\\Rating\\Controller'           => __DIR__ . '/../../Rating/Controller/',
            '\\OxidEsales\\GraphQL\\Account\\Review\\Controller'           => __DIR__ . '/../../Review/Controller/',
            '\\OxidEsales\\GraphQL\\Account\\WishedPrice\\Controller'      => __DIR__ . '/../../WishedPrice/Controller/',
            '\\OxidEsales\\GraphQL\\Account\\Account\\Controller'          => __DIR__ . '/../../Account/Controller/',
            '\\OxidEsales\\GraphQL\\Account\\NewsletterStatus\\Controller' => __DIR__ . '/../../NewsletterStatus/Controller/',
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            '\\OxidEsales\\GraphQL\\Account\\Rating\\DataType'                  => __DIR__ . '/../../Rating/DataType/',
            '\\OxidEsales\\GraphQL\\Account\\Rating\\Service'                   => __DIR__ . '/../../Rating/Service/',
            '\\OxidEsales\\GraphQL\\Account\\Review\\DataType'                  => __DIR__ . '/../../Review/DataType/',
            '\\OxidEsales\\GraphQL\\Account\\Review\\Service'                   => __DIR__ . '/../../Review/Service/',
            '\\OxidEsales\\GraphQL\\Account\\WishedPrice\\DataType'             => __DIR__ . '/../../WishedPrice/DataType/',
            '\\OxidEsales\\GraphQL\\Account\\WishedPrice\\Service'              => __DIR__ . '/../../WishedPrice/Service/',
            '\\OxidEsales\\GraphQL\\Account\\Account\\DataType'                 => __DIR__ . '/../../Account/DataType/',
            '\\OxidEsales\\GraphQL\\Account\\Account\\Service'                  => __DIR__ . '/../../Account/Service/',
            '\\OxidEsales\\GraphQL\\Account\\NewsletterStatus\\DataType'        => __DIR__ . '/../../NewsletterStatus/DataType/',
            '\\OxidEsales\\GraphQL\\Account\\NewsletterStatus\\Service'         => __DIR__ . '/../../NewsletterStatus/Service/',
            '\\OxidEsales\\GraphQL\\Account\\NewsletterStatus\\Infrastructure'  => __DIR__ . '/../../NewsletterStatus/Infrastructure/',
        ];
    }
}
