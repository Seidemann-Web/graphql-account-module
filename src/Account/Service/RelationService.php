<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\Customer;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;
use OxidEsales\GraphQL\Account\NewsletterStatus\Service\NewsletterStatus as NewsletterStatusService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=Customer::class)
 */
final class RelationService
{
    /** @var NewsletterStatusService */
    private $newsletterStatusService;

    public function __construct(
        NewsletterStatusService $newsletterStatusService
    ) {
        $this->newsletterStatusService = $newsletterStatusService;
    }

    /**
     * @Field()
     */
    public function getNewsletterStatus(): ?NewsletterStatusType
    {
        try {
            return $this->newsletterStatusService->newsletterStatus();
        } catch (NewsletterStatusNotFound $e) {
        }

        return null;
    }
}
