<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Exception;

use OxidEsales\GraphQL\Base\Exception\NotFound;

use function sprintf;

final class RatingExists extends NotFound
{
    public static function byId(string $id): self
    {
        return new self(sprintf("You already rated product '%s', please delete existing rating first.", $id));
    }
}
