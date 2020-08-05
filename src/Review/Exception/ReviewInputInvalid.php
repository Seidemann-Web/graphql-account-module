<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Review\Exception;

use OutOfBoundsException;

final class ReviewInputInvalid extends OutOfBoundsException
{
    public static function byWrongValue(): self
    {
        return new self('Review input cannot have both empty text and rating value.');
    }
}
