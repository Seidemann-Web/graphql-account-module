<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Controller;

use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusUnsubscribe as NewsletterStatusUnsubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusSubscribe as NewsletterStatusSubscribeType;
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

    /**
     * NewsletterStatusUnsubscribeInput email field is optional.
     * In case of missing input email but available token, newsletter will be unsubscribed for token email.
     * Input email is preferred over token email.
     *
     * @Mutation()
     */
    public function newsletterUnsubscribe(
        ?NewsletterStatusUnsubscribeType $newsletterStatus
    ): bool {
        return $this->newsletterStatusService->unsubscribe($newsletterStatus);
    }

    /**
     * @Mutation()
     */
    public function newsletterSubscribe(
        ?NewsletterStatusSubscribeType $newsletterStatus
    ): NewsletterStatusSubscribeType {
        return $this->newsletterStatusService->subscribe($newsletterStatus);
    }
}
