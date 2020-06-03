<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\Exception;

use function sprintf;

final class RatingOutOfBounds extends \OutOfBoundsException
{
    public function __construct(
        $message = 'Rating should be in 1 to 5 interval',
        $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
