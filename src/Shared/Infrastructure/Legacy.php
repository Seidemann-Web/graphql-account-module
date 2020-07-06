<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Shared\Infrastructure;

use OxidEsales\Eshop\Core\MailValidator as EhopMailValidator;

final class Legacy
{
    public function isValidEmail(string $email): bool
    {
        /** @var EhopMailValidator */
        $validator = oxNew(EhopMailValidator::class);

        return $validator->isValidEmail($email);
    }
}
