<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Contact\Infrastructure;

use OxidEsales\Eshop\Core\Email;
use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;
use OxidEsales\GraphQL\Account\Contact\DataType\ContactRequest;
use OxidEsales\GraphQL\Account\Contact\Exception\ContactRequestFieldsValidationError;
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

    /**
     * @var Email
     */
    private $mailer;

    public function __construct(
        Legacy $legacy,
        ContactFormBridgeInterface $contactFormBridge,
        Email $mailer
    ) {
        $this->legacy            = $legacy;
        $this->contactFormBridge = $contactFormBridge;
        $this->mailer            = $mailer;
    }

    /**
     * Validate contact request fields
     * throws exceptions on validation errors
     */
    public function assertValidContactRequest(ContactRequest $contactRequest): bool
    {
        $form = $this->contactFormBridge->getContactForm();
        $form->handleRequest($contactRequest->getFields());

        if (!$form->isValid()) {
            $errors = $form->getErrors();

            throw ContactRequestFieldsValidationError::byValidationFieldError(reset($errors));
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
        $form = $this->contactFormBridge->getContactForm();
        $form->handleRequest($contactRequest->getFields());
        $message = $this->contactFormBridge->getContactFormMessage($form);

        return $this->mailer->sendContactMail($contactRequest->getEmail(), $contactRequest->getSubject(), $message);
    }
}
