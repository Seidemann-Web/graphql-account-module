<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusSubscribe as NewsletterStatusSubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\InvalidEmail;
use OxidEsales\GraphQL\Account\NewsletterStatus\Infrastructure\Repository as NewsletterStatusRepository;
use TheCodingMachine\GraphQLite\Annotations\Factory;

final class NewsletterSubscribeInput
{
    /** @var NewsletterStatusRepository */
    private $newsletterStatusRepository;

    public function __construct(
        NewsletterStatusRepository $newsletterStatusRepository
    ) {
        $this->newsletterStatusRepository = $newsletterStatusRepository;
    }

    /**
     * @Factory
     */
    public function fromUserInput(
        ?string $firstName,
        ?string $lastName,
        ?string $salutation,
        string $email
    ): NewsletterStatusSubscribeType {
        $this->assertValidEmail($email);

        return new NewsletterStatusSubscribeType((string) $firstName, (string) $lastName, (string) $salutation, $email);
    }

    private function assertValidEmail(string $email): bool
    {
        if (!$this->newsletterStatusRepository->isValidEmail($email)) {
            throw new InvalidEmail();
        }

        return true;
    }
}
