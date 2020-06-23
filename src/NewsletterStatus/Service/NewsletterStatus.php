<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Infrastructure\Repository as NewsletterStatusRepository;
use OxidEsales\GraphQL\Account\NewsletterStatus\Service\Subscriber as SubscriberService;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class NewsletterStatus
{
    /** @var NewsletterStatusRepository */
    private $newsletterStatusRepository;

    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var SubscriberService */
    private $subscriberService;

    public function __construct(
        NewsletterStatusRepository $newsletterStatusRepository,
        Authentication $authenticationService,
        Repository $repository,
        SubscriberService $subscriberService
    ) {
        $this->newsletterStatusRepository = $newsletterStatusRepository;
        $this->authenticationService      = $authenticationService;
        $this->repository                 = $repository;
        $this->subscriberService          = $subscriberService;
    }

    public function newsletterStatus(): NewsletterStatusType
    {
        /** Only logged in users can query their newsletter status */
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        return $this->newsletterStatusRepository->getByUserId(
            $this->authenticationService->getUserId()
        );
    }

    public function optIn(NewsletterStatusType $newsletterStatus): bool
    {
        $subscriber = $this->subscriberService->subscriber((string) $newsletterStatus->userId());

        $modelItem = $newsletterStatus->getEshopModel();
        $modelItem->updateSubscription($subscriber->getEshopModel());
        $modelItem->setOptInStatus(1);

        return $this->repository->saveModel($modelItem);
    }

    public function save(NewsletterStatus $newsletterStatus): bool
    {
        $modelItem = $newsletterStatus->getEshopModel();
        $modelItem->updateSubscription($modelItem->getUser());

        return $this->repository->saveModel($modelItem);
    }
}
