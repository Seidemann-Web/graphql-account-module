<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Newsletter\Infrastructure;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\GraphQL\Account\Newsletter\DataType\NewsletterSubscriptionStatus;
use OxidEsales\GraphQL\Base\Exception\NotFound;

final class Repository
{
    public function getByUserId(
        string $userId
    ): NewsletterSubscriptionStatus {
        /** @var EshopNewsletterSubscriptionStatusModel */
        $model = oxNew(NewsletterSubscriptionStatus::getModelClass());

        if (!$model->loadFromUserId($userId)) {
            throw new NotFound($userId);
        }

        return new NewsletterSubscriptionStatus($model);
    }
}
