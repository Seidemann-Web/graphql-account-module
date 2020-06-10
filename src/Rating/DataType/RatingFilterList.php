<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\DataType;

use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Catalogue\DataType\FilterList;

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

    public function withUserFilter(StringFilter $user): self
    {
        $filter = clone $this;
        $filter->userId = $user;
        return $filter;
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
