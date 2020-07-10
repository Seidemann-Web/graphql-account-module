<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\Customer;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Account\Infrastructure\Repository as AccountRepository;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatus as NewsletterStatusType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;
use OxidEsales\GraphQL\Account\NewsletterStatus\Service\NewsletterStatus as NewsletterStatusService;
use OxidEsales\GraphQL\Account\Review\DataType\ReviewFilterList;
use OxidEsales\GraphQL\Account\Review\Service\Review as ReviewService;
use OxidEsales\GraphQL\Base\DataType\IDFilter;
use OxidEsales\GraphQL\Catalogue\Review\DataType\Review as ReviewDataType;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @ExtendType(class=Customer::class)
 */
final class RelationService
{
    /** @var ReviewService */
    private $reviewService;

    /** @var NewsletterStatusService */
    private $newsletterStatusService;

    /** @var AccountRepository */
    private $accountRepository;

    public function __construct(
        ReviewService $reviewService,
        NewsletterStatusService $newsletterStatusService,
        AccountRepository $accountRepository
    ) {
        $this->reviewService           = $reviewService;
        $this->newsletterStatusService = $newsletterStatusService;
        $this->accountRepository       = $accountRepository;
    }

    /**
     * @Field()
     *
     * @return ReviewDataType[]
     */
    public function getReviews(Customer $customer): array
    {
        return $this->reviewService->reviews(
            new ReviewFilterList(
                new IDFilter(
                    new ID(
                        (string) $customer->getId()
                    )
                )
            )
        );
    }

    /**
     * @Field()
     */
    public function getNewsletterStatus(): ?NewsletterStatusType
    {
        try {
            return $this->newsletterStatusService->newsletterStatus();
        } catch (NewsletterStatusNotFound $e) {
        }

        return null;
    }

    /**
     * @Field()
     *
     * @return DeliveryAddress[]
     */
    public function deliveryAddresses(Customer $customer): array
    {
        return $this->accountRepository->addresses($customer);
    }
}
