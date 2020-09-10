<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use Exception;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;
use OxidEsales\GraphQL\Account\Account\DataType\ContactRequest;
use OxidEsales\GraphQL\Base\Service\Legacy;

final class Contact
{
    /**
     * @var Legacy
     */
    private $legacy;

    /**
     * @var ContactFormBridgeInterface
     */
    private $contactFormBridge;

    public function __construct(
        Legacy $legacy,
        ContactFormBridgeInterface $contactFormBridge
    ) {
        $this->legacy = $legacy;
        $this->contactFormBridge = $contactFormBridge;
    }

    /**
     * Validate contact request fields
     * throws exceptions on validation errors
     *
     * @param string[] $fields
     *
     * @return bool
     */
    public function validateContactRequest(ContactRequest $contactRequest): bool
    {
        $form = $this->contactFormBridge->getContactForm();
        $form->handleRequest($contactRequest->getFields());

        if (!$form->isValid()) {
            $errors = $form->getErrors();
            throw new Exception(reset($errors));
        }

        return true;
    }

    public function getRequiredContactFormFields(): array
    {
        $contactFormRequiredFields = $this->legacy->getConfigParam('contactFormRequiredFields');

        return $contactFormRequiredFields === null ? [] : $contactFormRequiredFields;
    }

    public function sendRequest(ContactRequest $contactRequest): bool
    {
        $mailer = oxNew(Email::class);

        $form = $this->contactFormBridge->getContactForm();
        $form->handleRequest($contactRequest->getFields());
        $message = $this->contactFormBridge->getContactFormMessage($form);

        return (bool) $mailer->sendContactMail($contactRequest->getEmail(), $contactRequest->getSubject(), $message);
    }
}
