<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Infrastructure;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusUnsubscribe as NewsletterStatusUnsubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\Subscriber as SubscriberDataType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;

final class Repository
{
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
        return $subscriber->getEshopModel()->setNewsSubscription(false, false);
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

    //todo move eshop stuff here.
    //new function -> pass data types -> use eshop models here

}
