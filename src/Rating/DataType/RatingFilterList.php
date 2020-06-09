<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\DataType;

use OxidEsales\GraphQL\Base\DataType\StringFilter;

class RatingFilterList extends FilterList
{
    /** @var null|StringFilter */
    protected $userId;

    public function __construct(
        ?StringFilter $userId = null
    ) {
        $this->userId = $userId;

        parent::__construct();
    }

    /**
     * @return array{
     *  oxuserId: null|StringFilter
     * }
     */
    public function getFilters(): array
    {
        return [
            'oxuserId' => $this->userId
        ];
    }
}
