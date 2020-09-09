<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\Context;
use OxidEsales\GraphQL\Account\Account\DataType\ContactRequest;

final class Contact
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var ContactFormBridgeInterface
     */
    private $contactFormBridge;

    public function __construct(
        Context $context,
        ContactFormBridgeInterface $contactFormBridge
    ) {
        $this->context = $context;
        $this->contactFormBridge = $contactFormBridge;
    }

    /**
     * Validate contact request fields, returns array with errors
     *
     * @param string[] $fields
     *
     * @return array
     */
    public function validateContactFields(array $fields): array
    {
        $form = $this->contactFormBridge->getContactForm();
        $form->handleRequest($fields);

        return !$form->isValid() ? $form->getErrors() : [];
    }

    public function getRequiredContactFormFields(): array
    {
        return $this->context->getRequiredContactFormFields();
    }

    public function sendRequest(ContactRequest $contactRequest)
    {
        return true;
    }
}
