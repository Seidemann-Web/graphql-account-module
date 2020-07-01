<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Exception;

use Exception;
use Throwable;

final class IncorrectSalutation extends Exception
{
    public function __construct(string $message = 'Incorrect salutation. Please use "mr" or "mrs"', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
