<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Service\Authentication;

final class Address
{
    /** @var Authentication */
    private $authenticationService;

    public function __construct(
        Authentication $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    public function updateInvoiceAddress(InvoiceAddress $invoiceAddress): InvoiceAddress
    {
        if (!$id = (string) $this->authenticationService->getUserId()) {
            throw new InvalidLogin('Unauthorized');
        }

        $invoiceAddress->getEshopModel()->save();

        return $invoiceAddress;
    }
}
