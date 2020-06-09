<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\DataType;

use OxidEsales\GraphQL\Base\DataType\BoolFilter;
use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Catalogue\DataType\FilterList;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class WishedPriceFilterList extends FilterList
{
    /** @var ?StringFilter */
    private $userId = null;

    public function __construct(?StringFilter $userId)
    {
        $this->userId = $userId;
        parent::__construct();
    }

    /**
     * @Factory(name="WishedPriceFilterList")
     */
    public static function createWishedPriceFilterList(?StringFilter $userId): self
    {
        return new self($userId);
    }

    /**
     * @return array{
     *  oxuserId: ?StringFilter
     * }
     */
    public function getFilters(): array
    {
        return [
            'oxuserId' => $this->userId
        ];
    }
}
