<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\Country\Controller;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class CountryTest extends TokenTestCase
{
    private const ACTIVE_COUNTRY = 'a7c40f631fc920687.20179984';

    private const INACTIVE_COUNTRY  = 'a7c40f633038cd578.22975442';

    public function testGetSingleActiveCountry(): void
    {
        $result = $this->query('query {
            country (id: "' . self::ACTIVE_COUNTRY . '") {
                id
                active
                title
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertEquals(
            [
                'id'     => self::ACTIVE_COUNTRY,
                'active' => true,
                'title'  => 'Deutschland',
            ],
            $result['body']['data']['country']
        );
    }

    public function testGetSingleInactiveCountryWithoutToken(): void
    {
        $result = $this->query('query {
            country (id: "' . self::INACTIVE_COUNTRY . '") {
                id
                active
            }
        }');

        $this->assertResponseStatus(
            401,
            $result
        );
    }

    public function testGetSingleInactiveCountryWithToken(): void
    {
        $this->prepareToken();

        $result = $this->query('query {
            country (id: "' . self::INACTIVE_COUNTRY . '") {
                id
                active
            }
        }');

        $this->assertEquals(200, $result['status']);
        $this->assertEquals(
            [
                'id'     => self::INACTIVE_COUNTRY,
                'active' => false,
            ],
            $result['body']['data']['country']
        );
    }

    public function testGetSingleNonExistingCountry(): void
    {
        $result = $this->query('query {
            country (id: "DOES-NOT-EXIST") {
                id
            }
        }');

        $this->assertEquals(404, $result['status']);
    }

    public function testGetCountryListWithoutFilter(): void
    {
        $result = $this->query('query {
            countries {
                id
                active
                title
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );
        $this->assertCount(
            5,
            $result['body']['data']['countries']
        );
    }

    public function testGetCountryListWithPartialFilter(): void
    {
        $result = $this->query('query {
            countries(filter: {
                title: {
                    contains: "sch"
                }
            }) {
                id
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );
        $this->assertEquals(
            [
                ['id' => 'a7c40f631fc920687.20179984'],
                ['id' => 'a7c40f6321c6f6109.43859248'],
            ],
            $result['body']['data']['countries']
        );
    }

    public function testGetCountryListWithExactFilter(): void
    {
        $result = $this->query('query {
            countries(filter: {
                title: {
                    equals: "Deutschland"
                }
            }) {
                id,
                title
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );
        $this->assertSame(
            [
                [
                    'id'    => self::ACTIVE_COUNTRY,
                    'title' => 'Deutschland',
                ],
            ],
            $result['body']['data']['countries']
        );
    }

    public function testGetEmptyCountryListWithFilter(): void
    {
        $result = $this->query('query {
            countries(filter: {
                title: {
                    contains: "DOES-NOT-EXIST"
                }
            }) {
                id
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );
        $this->assertCount(
            0,
            $result['body']['data']['countries']
        );
    }
}
