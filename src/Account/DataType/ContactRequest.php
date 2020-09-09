<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\DataType;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
final class ContactRequest
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $message;

    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $salutation,
        string $subject,
        string $message
    ) {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->salutation = $salutation;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * @Field()
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @Field()
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @Field()
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @Field()
     */
    public function getSalutation(): string
    {
        return $this->salutation;
    }

    /**
     * @Field()
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @Field()
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return [
            'email' => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'salutation' => $this->getSalutation(),
            'subject' => $this->getSubject(),
            'message' => $this->getMessage()
        ];
    }
}
