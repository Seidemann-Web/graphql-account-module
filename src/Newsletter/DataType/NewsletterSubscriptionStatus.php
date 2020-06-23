<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Newsletter\DataType;

use DateTimeImmutable;
use DateTimeInterface;
use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class NewsletterSubscriptionStatus implements DataType
{
    /** @var array */
    private $statusMapping = [
        0 => 'unsubscribed',
        1 => 'subscribed',
        2 => 'missing double optin',
    ];

    /** @var EshopNewsletterSubscriptionStatusModel */
    private $newsletterSubscriptionStatus;

    public function __construct(
        EshopNewsletterSubscriptionStatusModel $newsletterSubscriptionStatus
    ) {
        $this->newsletterSubscriptionStatus = $newsletterSubscriptionStatus;
    }

    /**
     * @Field()
     */
    public function salutation(): string
    {
        return (string) $this->newsletterSubscriptionStatus->getFieldData('oxsal');
    }

    /**
     * @Field()
     */
    public function firstname(): string
    {
        return (string) $this->newsletterSubscriptionStatus->getFieldData('oxfname');
    }

    /**
     * @Field()
     */
    public function lastname(): string
    {
        return (string) $this->newsletterSubscriptionStatus->getFieldData('oxlname');
    }

    /**
     * @Field()
     */
    public function email(): string
    {
        return (string) $this->newsletterSubscriptionStatus->getFieldData('oxemail');
    }

    /**
     * @Field()
     */
    public function status(): string
    {
        $status = $this->newsletterSubscriptionStatus->getOptInStatus();

        if ($status < 0 || $status > 2) {
            $status = 0;
        }

        return $this->statusMapping[$status];
    }

    /**
     * @Field()
     */
    public function failedEmailCount(): int
    {
        return (int) $this->newsletterSubscriptionStatus->getFieldData('oxemailfailed');
    }

    /**
     * @Field()
     */
    public function subscribed(): DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->newsletterSubscriptionStatus->getFieldData('oxsubscribed')
        );
    }

    /**
     * @Field()
     */
    public function unsubscribed(): ?DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->newsletterSubscriptionStatus->getFieldData('oxunsubscribed')
        );
    }

    /**
     * @Field()
     */
    public function created(): DateTimeInterface
    {
        return new DateTimeImmutable(
            (string) $this->newsletterSubscriptionStatus->getFieldData('oxtimestamp')
        );
    }

    public function userId(): ID
    {
        return new ID(
            (string) $this->newsletterSubscriptionStatus->getFieldData('oxuserid')
        );
    }

    public static function getModelClass(): string
    {
        return EshopNewsletterSubscriptionStatusModel::class;
    }
}
