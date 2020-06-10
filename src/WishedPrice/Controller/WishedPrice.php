<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\Controller;

use OxidEsales\GraphQL\Account\WishedPrice\DataType\WishedPrice as WishedPriceDataType;
use OxidEsales\GraphQL\Account\WishedPrice\Service\WishedPrice as WishedPriceService;
use OxidEsales\GraphQL\Account\WishedPrice\DataType\WishedPriceFilterList;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Service\Repository;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class WishedPrice
{
    /** @var WishedPriceService */
    private $wishedPriceService = null;

    public function __construct(
        WishedPriceService $wishedPriceService
    ) {
        $this->wishedPriceService = $wishedPriceService;
    }

    /**
     * @Query()
     *
     * @return WishedPriceDataType
     */
    public function wishedPrice(string $id): WishedPriceDataType
    {
        return $this->wishedPriceService->wishedPrice($id);
    }

    /**
     * @Mutation()
     *
     * @return WishedPriceDataType
     */
    public function wishedPriceDelete(string $id): WishedPriceDataType
    {
        return $this->wishedPriceService->delete($id);
    }

    /**
     * @Query()
     *
     * @throws InvalidToken
     * @return WishedPriceDataType[]
     */
    public function wishedPrices(): array
    {
        return $this->wishedPriceService->wishedPrices(
            new WishedPriceFilterList()
        );
    }
}
