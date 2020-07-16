<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Shared\DataType;

use OxidEsales\GraphQL\Base\DataType\IDFilter;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\FilterList;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class BasketItemFilterList extends FilterList
{
    /** @var ?IDFilter */
    private $basket;

    public function __construct(
        ?IDFilter $basket = null
    ) {
        $this->basket = $basket;
        parent::__construct();
    }

    /**
     * @return array{
     *                oxbasketid: ?IDFilter,
     *                }
     */
    public function getFilters(): array
    {
        return [
            'oxbasketid' => $this->basket,
        ];
    }

    /**
     * @Factory(name="BasketItemFilterList")
     */
    public static function createBasketItemFilterList(
        ?IDFilter $basket = null
    ): self {
        return new self($basket);
    }
}
