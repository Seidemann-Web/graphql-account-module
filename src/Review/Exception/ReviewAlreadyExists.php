<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Review\Exception;

use OxidEsales\GraphQL\Base\Exception\NotFound;

use function sprintf;

final class ReviewAlreadyExists extends NotFound
{
    public static function byObjectId(string $id): self
    {
        return new self(sprintf('Review for product with id: %s already exists', $id));
    }
}
