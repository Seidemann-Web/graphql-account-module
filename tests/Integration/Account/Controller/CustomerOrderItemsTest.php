<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Account\Controller;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class CustomerOrderItemsTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const ORDER_WITH_NON_EXISTING_PRODUCT = '_order_with_non_existing_product';

    private const ORDER_WITH_DELETED_PRODUCT = '_order_with_deleted_product';

    public function testCustomerOrderItems(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->query(
            'query {
                customer {
                    id
                    orders(pagination: { limit: 1, offset: 3 }) {
                        id
                        orderNumber
                        items {
                            id
                            amount
                            product {
                                id
                                title
                            }
                            sku
                            title
                            shortDescription
                            price {
                                price
                                vat
                            }
                            itemPrice {
                                price
                            }
                            dimensions {
                                length
                                width
                                height
                                weight
                            }
                            insert
                            cancelled
                            bundle
                        }
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $items = $result['body']['data']['customer']['orders'][0]['items'];

        $this->assertCount(6, $items);

        $expectedItems = [
            [
                'id'               => '7d0e255c591fb5062669fd039bcf9f29',
                'amount'           => 1.0,
                'product'          => [
                    'id'    => '058e613db53d782adfc9f2ccb43c45fe',
                    'title' => 'Bindung O&#039;BRIEN DECADE CT 2010',
                ],
                'sku'              => '2401',
                'title'            => 'Bindung O\'BRIEN DECADE CT 2010',
                'shortDescription' => 'Geringes Gewicht, beste Performance!',
                'price'            => [
                    'price' => 359.0,
                    'vat'   => 19.0,
                ],
                'itemPrice'        => [
                    'price' => 359.0,
                ],
                'dimensions'       => [
                    'length' => 0.0,
                    'width'  => 0.0,
                    'height' => 0.0,
                    'weight' => 0.0,
                ],
                'insert'           => '2006-12-20T00:00:00+01:00',
                'cancelled'        => false,
                'bundle'           => false,
            ], [
                'id'               => '7d010996ab5656e369a63cdccb5f56e7',
                'amount'           => 1.0,
                'product'          => [
                    'id'    => 'b56369b1fc9d7b97f9c5fc343b349ece',
                    'title' => 'Kite CORE GTS',
                ],
                'sku'              => '1208',
                'title'            => 'Kite CORE GTS',
                'shortDescription' => 'Die Sportversion des GT',
                'price'            => [
                    'price' => 879.0,
                    'vat'   => 19.0,
                ],
                'itemPrice'        => [
                    'price' => 879.0,
                ],
                'dimensions'       => [
                    'length' => 0.0,
                    'width'  => 0.0,
                    'height' => 0.0,
                    'weight' => 0.0,
                ],
                'insert'           => '2015-12-20T00:00:00+01:00',
                'cancelled'        => false,
                'bundle'           => false,
            ],
        ];

        foreach ($items as $item) {
            foreach ($expectedItems as $expectedItem) {
                if ($expectedItem['id'] != $item['id']) {
                    continue;
                }

                $this->assertSame($item, $expectedItem);
            }
        }
    }

    public function testCustomerOrderItemsWithNonExistingProduct(): void
    {
        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);

        $result = $this->query(
            'query {
                customer {
                    id
                    orders {
                        id
                        items {
                            id
                            product {
                                id
                            }
                        }
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $orders = $result['body']['data']['customer']['orders'];

        foreach ($orders as $order) {
            if ($order['id'] != self::ORDER_WITH_NON_EXISTING_PRODUCT) {
                continue;
            }

            $this->assertNull($order['items'][0]['product']);
        }
    }

    public function testCustomerOrderItemsWithInactiveProduct(): void
    {
        $this->updateProductActiveStatus('_test_product_for_basket', false);

        $this->prepareToken(self::OTHER_USERNAME, self::PASSWORD);

        $result = $this->query(
            'query {
                customer {
                    id
                    orders {
                        id
                        items {
                            id
                            product {
                                id
                            }
                        }
                    }
                }
            }'
        );

        $this->assertResponseStatus(200, $result);

        $orders = $result['body']['data']['customer']['orders'];

        foreach ($orders as $order) {
            if ($order['id'] != self::ORDER_WITH_DELETED_PRODUCT) {
                continue;
            }

            $this->assertNull($order['items'][0]['product']);
        }

        $this->updateProductActiveStatus('_test_product_for_basket', true);
    }

    private function updateProductActiveStatus(string $productId, bool $active): void
    {
        $queryBuilderFactory = ContainerFactory::getInstance()
            ->getContainer()
            ->get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();

        $queryBuilder
            ->update('oxarticles')
            ->set('oxactive', (int) $active)
            ->where('OXID = :OXID')
            ->setParameter(':OXID', $productId)
            ->execute();
    }
}
