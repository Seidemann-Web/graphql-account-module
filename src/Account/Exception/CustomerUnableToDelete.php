<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Exception;

use OxidEsales\GraphQL\Base\Exception\NotFound;

final class CustomerUnableToDelete extends NotFound //TODO: probably should be something else
{
    public static function disabledByShopAdmin(): self
    {
        return new self('Deleting account disabled by shop admin!');
    }

    public static function mallAdmin(): self
    {
        return new self('Unable to delete account marked as mall admin!');
    }
}
