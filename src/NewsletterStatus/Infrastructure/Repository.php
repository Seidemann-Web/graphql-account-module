<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Infrastructure;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\Eshop\Core\MailValidator as EhopMailValidator;
use OxidEsales\GraphQL\Account\Account\Infrastructure\Repository as CustomerRepository;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusSubscribe as NewsletterStatusSubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusUnsubscribe as NewsletterStatusUnsubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\Subscriber as SubscriberDataType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;
use OxidEsales\GraphQL\Account\NewsletterStatus\Service\Subscriber as SubscriberService;

final class Repository
{
    /** @var CustomerRepository */
    private $customerRepository;

    /** @var SubscriberService */
    private $subscriberService;

    public function __construct(
        CustomerRepository $customerRepository,
        SubscriberService $subscriberService
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriberService  = $subscriberService;
    }

    /**
     * @throws NewsletterStatusNotFound
     */
    public function getByUserId(
        string $userId
    ): NewsletterStatusType {
        /** @var EshopNewsletterSubscriptionStatusModel */
        $model = oxNew(NewsletterStatusType::getModelClass());

        if (!$model->loadFromUserId($userId)) {
            throw NewsletterStatusNotFound::byUserId($userId);
        }

        return new NewsletterStatusType($model);
    }

    public function getByEmail(string $email): NewsletterStatusType
    {
        return new NewsletterStatusType($this->getEhopModelByEmail($email));
    }

    public function getUnsubscribeByEmail(string $email): NewsletterStatusUnsubscribeType
    {
        return new NewsletterStatusUnsubscribeType($this->getEhopModelByEmail($email));
    }

    public function optIn(SubscriberDataType $subscriber, NewsletterStatusType $newsletterStatus): bool
    {
        /** @var EshopNewsletterSubscriptionStatusModel $newsletterStatusModel */
        $newsletterStatusModel = $newsletterStatus->getEshopModel();
        $newsletterStatusModel->setOptInStatus(1);

        return $newsletterStatusModel->updateSubscription($subscriber->getEshopModel());
    }

    public function unsubscribe(SubscriberDataType $subscriber): bool
    {
        return $this->subscriberService->setNewsSubscription($subscriber, false);
    }

    public function subscribe(SubscriberDataType $subscriber): NewsletterStatusType
    {
        $this->subscriberService->setNewsSubscription($subscriber, true);

        return $this->getByEmail($subscriber->getUserName());
    }

    public function subscribeFromInput(
        NewsletterStatusSubscribeType $newsletterStatusSubscribeInput
    ): NewsletterStatusType {
        try {
            $newsletterStatus = $this->getByEmail($newsletterStatusSubscribeInput->email());
            $subscriber       = $this->subscriberService->subscriber((string) $newsletterStatus->userId());
        } catch (NewsletterStatusNotFound $exception) {
            $customer   = $this->customerRepository->createNewsletterUser($newsletterStatusSubscribeInput);
            $subscriber = new SubscriberDataType($customer->getEshopModel());
        }

        $this->unsubscribe($subscriber);

        return $this->subscribe($subscriber);
    }

    public function isValidEmail(string $email): bool
    {
        /** @var EhopMailValidator $mailValidator */
        $mailValidator = oxNew(EhopMailValidator::class);

        return $mailValidator->isValidEmail($email);
    }

    /**
     * @throws NewsletterStatusNotFound
     */
    private function getEhopModelByEmail(string $email): EshopNewsletterSubscriptionStatusModel
    {
        /** @var EshopNewsletterSubscriptionStatusModel $newsletterStatusModel */
        $newsletterStatusModel = oxNew(NewsletterStatusType::getModelClass());

        if (!$newsletterStatusModel->loadFromEmail($email)) {
            throw NewsletterStatusNotFound::byEmail($email);
        }

        return $newsletterStatusModel;
    }
}
