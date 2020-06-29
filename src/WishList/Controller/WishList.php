<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishList\Controller;

use OxidEsales\GraphQL\Account\WishList\Service\WishList as WishListService;

final class WishList
{
    /** @var WishListService */
    private $wishListService;

    public function __construct(
        WishListService $wishListService
    ) {
        $this->wishListService = $wishListService;
    }
}
