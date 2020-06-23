<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\Eshop\Application\Model\NewsSubscribed;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactory;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class NewsletterUnsubscribeInput
{
    /** @var QueryBuilderFactory */
    private $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactory $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $email): NewsletterStatus
    {
        $this->assertEmailNotEmpty($email);

        /** @var NewsSubscribed $newsletterModelItem */
        $newsletterModelItem = oxNew(NewsletterStatus::getModelClass());
        $newsletterModelItem->loadFromEmail($email);

        /** @var User $user */
        $user = $this->getSubscribedUserByEmail($email);
        $user->removeFromGroup('oxidnewsletter');

        $newsletterModelItem->setOptInStatus(0);

        return new NewsletterStatus($newsletterModelItem);
    }

    private function assertEmailNotEmpty(string $email): bool
    {
        if (!strlen($email)) {
            throw new \Exception("Email empty");
        }

        return true;
    }

    private function getSubscribedUserByEmail(string $email): User
    {
        $qb = $this->queryBuilderFactory->create();
        $result = $qb->select("OXUSERID")
            ->from("oxnewssubscribed")
            ->where("OXEMAIL = :oxemail")
            ->setParameter("oxemail", $email)
            ->execute();

        $oxid = $result->fetchColumn();

        if (!$oxid) {
            throw new \Exception("Cannot find any subscribed user with this email");
        }

        $user = oxNew(User::class);
        $user->load($oxid);

        return $user;
    }
}
