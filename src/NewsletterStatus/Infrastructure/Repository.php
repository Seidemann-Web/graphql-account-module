<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Infrastructure;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;

final class Repository
{
    /**
     * @throws NewsletterStatusNotFound
     */
    public function getByUserId(
        string $userId
    ): NewsletterStatus {
        /** @var EshopNewsletterSubscriptionStatusModel */
        $model = oxNew(NewsletterStatus::getModelClass());

        if (!$model->loadFromUserId($userId)) {
            throw NewsletterStatusNotFound::byUserId($userId);
        }

        return new NewsletterStatus($model);
    }
}
