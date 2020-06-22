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
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\EmailConfirmationCode;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\EmailEmpty;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class NewsletterOptInInput
{
    /** @var QueryBuilderFactory */
    private $queryBuilderFactory;

    /** @var int */
    private $shopid;

    public function __construct(
        QueryBuilderFactory $queryBuilderFactory,
        ContextInterface $context
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->shopid              = $context->getCurrentShopId();
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $email, string $confirmCode): NewsletterStatusType
    {
        $this->assertEmailNotEmpty($email);
        $user = $this->getUserByEmailConfirmCode($email, $confirmCode);

        /** @var NewsSubscribed $newsletterModelItem */
        $newsletterModelItem = oxNew(NewsletterStatusType::getModelClass());

        $newsletterModelItem->loadFromUserId($user->getId());
        $newsletterModelItem->setUser($user);
        $newsletterModelItem->assign([
            'OXUSERID' => $user->getId(),
        ]);

        return new NewsletterStatusType($newsletterModelItem);
    }

    private function assertEmailNotEmpty(string $email): bool
    {
        if (!strlen($email)) {
            throw new EmailEmpty();
        }

        return true;
    }

    private function getUserByEmailConfirmCode(string $email, string $confirmCode): User
    {
        $qb     = $this->queryBuilderFactory->create();
        $result = $qb->select('OXID')
            ->from('oxuser')
            ->andWhere('OXUSERNAME = :oxusername')
            ->andWhere('OXSHOPID = :shopid')
            ->andWhere(':confirmCode = md5(concat(OXUSERNAME, OXPASSSALT))')
            ->setParameter('shopid', $this->shopid)
            ->setParameter('oxusername', $email)
            ->setParameter('confirmCode', $confirmCode)
            ->execute();

        $oxid = $result->fetchColumn();

        if (!$oxid) {
            throw new EmailConfirmationCode();
        }

        $user = oxNew(User::class);
        $user->load($oxid);

        return $user;
    }
}
