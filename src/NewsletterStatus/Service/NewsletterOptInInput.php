<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\Subscriber as SubscriberType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\EmailConfirmationCode;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\EmailEmpty;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\SubscriberNotFound;
use OxidEsales\GraphQL\Account\NewsletterStatus\Service\Subscriber as SubscriberService;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class NewsletterOptInInput
{
    /** @var SubscriberService */
    private $subscriberService;

    public function __construct(
        SubscriberService $subscriberService
    ) {
        $this->subscriberService   = $subscriberService;
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $email, string $confirmCode): NewsletterStatusType
    {
        $this->assertEmailNotEmpty($email);

        /** @var EshopNewsletterSubscriptionStatusModel $newsletterStatusModel */
        $newsletterStatusModel = oxNew(NewsletterStatusType::getModelClass());

        if (!$newsletterStatusModel->loadFromEmail($email)) {
            throw NewsletterStatusNotFound::byEmail($email);
        }
        $newsletterStatus = new NewsletterStatusType($newsletterStatusModel);

        try {
            /** @var SubscriberType $subscriber */
            $subscriber = $this->subscriberService->subscriber((string) $newsletterStatus->userId());
        } catch (SubscriberNotFound $exception) {
            throw NewsletterStatusNotFound::byEmail($email);
        }

        $this->verifyConfirmCode($subscriber, $confirmCode);

        return $newsletterStatus;
    }

    private function assertEmailNotEmpty(string $email): bool
    {
        if (!strlen($email)) {
            throw new EmailEmpty();
        }

        return true;
    }

    /**
     * @throws EmailConfirmationCode
     */
    private function verifyConfirmCode(SubscriberType $subcriber, string $confirmCode): void
    {
        if ($subcriber->getConfirmationCode() !== $confirmCode) {
            throw new EmailConfirmationCode();
        }
    }
}
