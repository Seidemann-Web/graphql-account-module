<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use Exception;
use OxidEsales\GraphQL\Account\Account\DataType\ContactRequest;
use OxidEsales\GraphQL\Account\Account\Exception\InvalidEmail;
use OxidEsales\GraphQL\Account\Account\Infrastructure\Contact as ContactInfrastructure;
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

        $errors = $this->contactInfrastructure->validateContactFields([
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'salutation' => $salutation,
            'subject' => $subject,
            'message' => $message
        ]);

        if ($errors) {
            throw new Exception(reset($errors));
        }

        return new ContactRequest(
            $email,
            $firstName,
            $lastName,
            $salutation,
            $subject,
            $message
        );
    }
}
