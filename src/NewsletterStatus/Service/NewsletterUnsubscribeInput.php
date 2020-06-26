<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusUnsubscribe as NewsletterStatusUnsubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\EmailEmpty;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class NewsletterUnsubscribeInput
{
    /**
     * @Factory
     */
    public function fromUserInput(string $email): NewsletterStatusUnsubscribeType
    {
        $this->assertEmailNotEmpty($email);

        /** @var EshopNewsletterSubscriptionStatusModel $newsletterStatusModel */
        $newsletterStatusModel = oxNew(NewsletterStatusUnsubscribeType::getModelClass());

        if (!$newsletterStatusModel->loadFromEmail($email)) {
            throw NewsletterStatusNotFound::byEmail($email);
        }

        return new NewsletterStatusUnsubscribeType($newsletterStatusModel);
    }

    private function assertEmailNotEmpty(string $email): bool
    {
        if (!strlen($email)) {
            throw new EmailEmpty();
        }

        return true;
    }
}
