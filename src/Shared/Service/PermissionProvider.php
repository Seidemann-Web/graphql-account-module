<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Shared\Service;

use OxidEsales\GraphQL\Base\Framework\PermissionProviderInterface;

final class PermissionProvider implements PermissionProviderInterface
{
    public function getPermissions(): array
    {
        return [
            'oxidadmin' => [
                'VIEW_WISHED_PRICES',
                'DELETE_WISHED_PRICE',
                'VIEW_RATINGS',
                'DELETE_RATING',
                'DELETE_REVIEW',
                'VIEW_INACTIVE_COUNTRY',
                'DELETE_DELIVERY_ADDRESS',
                'DELETE_BASKET',
            ],
        ];
    }
}
