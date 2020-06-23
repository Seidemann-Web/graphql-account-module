<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Newsletter\Service;

use OxidEsales\GraphQL\Account\Newsletter\DataType\NewsletterSubscriptionStatus;
use OxidEsales\GraphQL\Account\Newsletter\Infrastructure\Repository;
use OxidEsales\GraphQL\Base\Service\Authentication;

final class Newsletter
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService
    ) {
        $this->repository            = $repository;
        $this->authenticationService = $authenticationService;
    }

    public function newsletterSubscriptionStatus(): NewsletterSubscriptionStatus
    {
        return $this->repository->getByUserId(
            $this->authenticationService->getUserId()
        );
    }
}
