<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Contact\Service;

use Exception;
use OxidEsales\GraphQL\Account\Contact\DataType\ContactRequest;
use OxidEsales\GraphQL\Account\Contact\Infrastructure\Contact as ContactInfrastructure;
use OxidEsales\GraphQL\Base\Service\Legacy;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class ContactRequestInput
{
    /** @var Legacy */
    private $legacyService;

    /** @var ContactInfrastructure */
    private $contactInfrastructure;

    public function __construct(
        Legacy $legacyService,
        ContactInfrastructure $contactInfrastructure
    ) {
        $this->legacyService = $legacyService;
        $this->contactInfrastructure = $contactInfrastructure;
    }

    /**
     * @Factory
     */
    public function fromUserInput(
        string $email = '',
        string $firstName = '',
        string $lastName = '',
        string $salutation = '',
        string $subject = '',
        string $message = ''
    ): ContactRequest {
        $contactRequest = new ContactRequest(
            $email,
            $firstName,
            $lastName,
            $salutation,
            $subject,
            $message
        );

        $this->contactInfrastructure->assertValidContactRequest($contactRequest);

        return $contactRequest;
    }
}
