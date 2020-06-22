<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Controller;

use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Service\NewsletterStatus as NewsletterStatusService;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class NewsletterStatus
{
    /** @var NewsletterStatusService */
    private $newsletterStatusService;

    public function __construct(
        NewsletterStatusService $newsletterStatusService
    ) {
        $this->newsletterStatusService = $newsletterStatusService;
    }

    /**
     * @Mutation()
     */
    public function newsletterOptIn(NewsletterStatusType $newsletterStatus): NewsletterStatusType
    {
        $this->newsletterStatusService->optIn($newsletterStatus);

        return $newsletterStatus;
    }
}
