<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Review\Infrastructure;

use OxidEsales\Eshop\Application\Model\Rating as EshopRatingModel;
use OxidEsales\Eshop\Application\Model\Rating as RatingEshopModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Review\DataType\Review as ReviewDataType;
use PDO;
use function getViewName;

final class Repository
{
    /** @var QueryBuilderFactoryInterface */
    private $queryBuilderFactory;

    /** @var RatingEshopModel */
    private $eshopRatingModel;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        EshopRatingModel $eshopRatingModel
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->eshopRatingModel    = $eshopRatingModel;
    }

    /**
     * @return true
     */
    public function delete(ReviewDataType $review): bool
    {
        try {
            $rating = $this->ratingForReview($review);
            $rating->delete();
        } catch (NotFound $e) {
        }
        $review->getEshopModel()->delete();

        return true;
    }

    public function saveRating(ReviewDataType $review): bool
    {
        $this->eshopRatingModel->assign(
            [
                'oxuserid'   => $review->getReviewerId(),
                'oxobjectid' => $review->getObjectId(),
                'oxrating'   => $review->getRating(),
                'oxtype'     => 'oxarticle',
            ]
        );
        $this->eshopRatingModel->save();

        return true;
    }

    /**
     * Sadly there is no relation between oxratings and oxreviews table but the
     * oxuserid, oxobject and oxrating values beeing identical ...
     */
    private function ratingForReview(ReviewDataType $review): RatingEshopModel
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->select('ratings.*')
            ->from(getViewName('oxratings'), 'ratings')
            ->where('oxuserid = :userid')
            ->andWhere('oxobjectid = :object')
            ->andWhere('oxrating = :rating')
            ->setParameters(
                [
                    'userid' => $review->getReviewerId(),
                    'object' => $review->getObjectId(),
                    'rating' => $review->getRating(),
                ]
            )
            ->setMaxResults(1);
        /** @var \Doctrine\DBAL\Statement $result */
        $result = $queryBuilder->execute();

        if ($result->rowCount() !== 1) {
            throw new NotFound();
        }

        /** @var RatingEshopModel */
        $model = oxNew(RatingEshopModel::class);
        $model->assign(
            $result->fetch(
                PDO::FETCH_ASSOC
            )
        );

        return $model;
    }
}
