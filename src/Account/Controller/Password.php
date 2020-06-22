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
    private $paswordService;

    public function __construct(
        PasswordService $paswordService
    ) {
        $this->paswordService = $paswordService;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function userPaswordChange(string $old, string $new): bool
    {
        return $this->paswordService->change($old, $new);
    }
}
