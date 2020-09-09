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

        $requiredFields = $this->contactInfrastructure->getRequiredContactFormFields();

        foreach ($requiredFields as $oneField) {
            if (!isset($$oneField) || strlen($$oneField) === 0) {
                throw new Exception("Value for {$oneField} is required");
            }
        }

        if (!$this->legacyService->isValidEmail($email)) {
            throw InvalidEmail::byString($email);
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
