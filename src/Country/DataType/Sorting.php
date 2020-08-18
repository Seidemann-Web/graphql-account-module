<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\DataType;

use OxidEsales\GraphQL\Base\DataType\Sorting as BaseSorting;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class Sorting extends BaseSorting
{
    /**
     * @Factory(name="CountrySorting")
     */
    public static function fromUserInput(
        ?string $position = null,
        ?string $title = null
    ): self {
        return new self([
            'oxorder' => $position,
            'oxtitle' => $title,
        ]);
    }
}
