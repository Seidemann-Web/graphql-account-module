<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\DataType;

use OxidEsales\GraphQL\Base\DataType\Sorting as BaseSorting;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class CountrySorting extends BaseSorting
{
    public function __construct(array $sorting)
    {
        $sorting = $sorting ?: ['oxorder' => self::SORTING_ASC];

        parent::__construct($sorting);
    }

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
