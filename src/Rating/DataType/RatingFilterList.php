<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\DataType;

use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\FilterList;

final class RatingFilterList extends FilterList
{
    /** @var null|StringFilter */
    protected $userId;

    /** @var null|StringFilter */
    protected $productId;

    public function __construct(
        ?StringFilter $userId = null,
        ?StringFilter $productId = null
    ) {
        $this->userId    = $userId;
        $this->productId = $productId;

        parent::__construct();
    }

    public function withUserFilter(StringFilter $user): self
    {
        $filter         = clone $this;
        $filter->userId = $user;

        return $filter;
    }

    /**
     * @return array{
     *                oxuserid: null|StringFilter
     *                }
     */
    public function getFilters(): array
    {
        return [
            'oxuserid'   => $this->userId,
            'oxobjectid' => $this->productId,
        ];
    }
}
