<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Rating\DataType;

use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Catalogue\DataType\Rating;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class RatingInput
{
    private $authentication;

    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $productId, int $rating): Rating
    {
        $model = oxNew(\OxidEsales\Eshop\Application\Model\Rating::class);
        $model->assign([
            'OXTYPE' => 'oxarticle',
            'OXOBJECTID' => $productId,
            'OXRATING' => $rating,
            'OXUSERID' => $this->authentication->getUserId()
        ]);

        return new Rating($model);
    }
}
