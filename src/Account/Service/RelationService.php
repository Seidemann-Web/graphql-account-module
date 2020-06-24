<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\Customer;
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

    public function __construct(
        ReviewService $reviewService
    ) {
        $this->reviewService = $reviewService;
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
}
