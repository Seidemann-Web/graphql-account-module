<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\WishedPrice\Exception;

use OutOfBoundsException;
use function sprintf;

final class WishedPriceOutOfBounds extends OutOfBoundsException
{
    public static function byWrongValue(string $value): self
    {
        return new self(sprintf('Wished price must be positive float, was %s', $value));
    }
}
