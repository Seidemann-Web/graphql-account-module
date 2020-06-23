<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Newsletter\Controller;

use OxidEsales\GraphQL\Account\Newsletter\DataType\NewsletterSubscriptionStatus as NewsletterSubscriptionStatus;
use OxidEsales\GraphQL\Account\Newsletter\Service\Newsletter as NewsletterService;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Newsletter
{
    /** @var NewsletterService */
    private $newsletterService;

    public function __construct(
        NewsletterService $newsletterService
    ) {
        $this->newsletterService = $newsletterService;
    }

    /**
     * @Query()
     */
    public function newsletterStatus(): NewsletterSubscriptionStatus
    {
        return $this->newsletterService->newsletterSubscriptionStatus();
    }
}
