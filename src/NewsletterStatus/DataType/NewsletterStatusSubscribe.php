<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\DataType;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
final class NewsletterStatusSubscribe
{
    /** @var string */
    private $lastName;

    /** @var string */
    private $firstName;

    /** @var string */
    private $salutation;

    /** @var string */
    private $email;

    public function __construct(
        string $firstName,
        string $lastName,
        string $salutation,
        string $email
    ) {
        $this->salutation = $salutation;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->email      = $email;
    }

    /**
     * @Field()
     */
    public function salutation(): string
    {
        return $this->salutation;
    }

    /**
     * @Field()
     */
    public function firstName(): string
    {
        return $this->firstName;
    }

    /**
     * @Field()
     */
    public function lastName(): string
    {
        return $this->lastName;
    }

    /**
     * @Field()
     */
    public function email(): string
    {
        return $this->email;
    }
}
