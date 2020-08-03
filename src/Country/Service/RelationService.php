<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Country\Service;

use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Country\DataType\State as StateDataType;
use OxidEsales\GraphQL\Account\Country\DataType\StateFilterList;
use OxidEsales\GraphQL\Account\Country\Service\State as StateService;
use OxidEsales\GraphQL\Base\DataType\IDFilter;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @ExtendType(class=CountryDataType::class)
 */
final class RelationService
{
    /** @var StateService */
    private $stateService;

    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * @Field()
     *
     * @return StateDataType[]
     */
    public function states(
        CountryDataType $country
    ): array {
        return $this->stateService->states(
            new StateFilterList(
                new IDFilter(
                    new ID(
                        (string) $country->getId()
                    )
                )
            )
        );
    }
}
