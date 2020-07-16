<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Basket\Controller;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Service\Basket as BasketService;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Basket
{
    /** @var BasketService */
    private $basketService;

    public function __construct(
        BasketService $basketService
    ) {
        $this->basketService = $basketService;
    }

    /**
     * @Query()
     */
    public function basket(string $id): BasketDataType
    {
        return $this->basketService->basket($id);
    }
}
