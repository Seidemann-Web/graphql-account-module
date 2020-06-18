<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Password\Service;

use OxidEsales\GraphQL\Account\Password\Exception\PasswordMismatch;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Catalogue\User\Service\User as UserService;

final class Password
{
    /** @var Repository */
    private $repository;

    /** @var UserService */
    private $userService;

    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        Repository $repository,
        UserService $userService,
        Authentication $authenticationService
    ) {
        $this->repository             = $repository;
        $this->userService            = $userService;
        $this->authenticationService  = $authenticationService;
    }

    public function change(string $old, string $new): bool
    {
        $userModel = $this->userService
                          ->user(
                              $this->authenticationService->getUserId()
                          )
                          ->getEshopModel();

        if (!$userModel->isSamePassword($old)) {
            throw PasswordMismatch::byOldPassword();
        }

        $userModel->setPassword($new);

        return $this->repository->saveModel($userModel);
    }
}
