<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Controller;

use OxidEsales\GraphQL\Account\Account\Service\Password as PasswordService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class Password
{
    /** @var PasswordService */
    private $passwordService;

    public function __construct(
        PasswordService $passwordService
    ) {
        $this->passwordService = $passwordService;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function customerPasswordChange(string $old, string $new): bool
    {
        return $this->passwordService->change($old, $new);
    }
}
