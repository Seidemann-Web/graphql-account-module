<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\DataType;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\DataType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Type()
 */
final class NewsletterStatusSubscribe implements DataType
{
    /** @var EshopNewsletterSubscriptionStatusModel */
    private $newsletterSubscriptionStatus;

    public function __construct(
        EshopNewsletterSubscriptionStatusModel $newsletterSubscriptionStatus
    ) {
        $this->newsletterSubscriptionStatus = $newsletterSubscriptionStatus;
    }

    public function getEshopModel(): EshopNewsletterSubscriptionStatusModel
    {
        return $this->newsletterSubscriptionStatus;
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
