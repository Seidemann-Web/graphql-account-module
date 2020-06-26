<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\Subscriber as SubscriberDataType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\SubscriberNotFound;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Subscriber
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws SubscriberNotFound
     */
    public function subscriber(string $id): SubscriberDataType
    {
        try {
            /** @var SubscriberDataType $Subscriber */
            $Subscriber = $this->repository->getById($id, SubscriberDataType::class);
        } catch (NotFound $e) {
            throw SubscriberNotFound::byId($id);
        }

        return $Subscriber;
    }
}
