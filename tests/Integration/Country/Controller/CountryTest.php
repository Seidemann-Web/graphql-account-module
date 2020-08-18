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

    private const COUNTRY_WITH_STATES  = '8f241f11096877ac0.98748826';

    public function testGetSingleActiveCountry(): void
    {
        $result = $this->query('query {
            country (id: "' . self::ACTIVE_COUNTRY . '") {
                id
                active
                title
                states {
                    id
                }
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
                'states' => [],
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

    public function testGetCountryListWithReversePositionSorting(): void
    {
        $result = $this->query('query {
            countries(sorting: {position: "DESC"}) {
                id
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );

        $this->assertEquals(
            [
                ['id' => 'a7c40f632a0804ab5.18804076'],
                ['id' => 'a7c40f6320aeb2ec2.72885259'],
                ['id' => 'a7c40f6321c6f6109.43859248'],
                ['id' => '8f241f11096877ac0.98748826'],
                ['id' => 'a7c40f631fc920687.20179984'],
            ],
            $result['body']['data']['countries']
        );
    }

    public function testGetCountryListWithTitleSorting(): void
    {
        $result = $this->query('query {
            countries(sorting: {title: "ASC"}) {
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
                ['id' => 'a7c40f6320aeb2ec2.72885259'],
                ['id' => 'a7c40f6321c6f6109.43859248'],
                ['id' => '8f241f11096877ac0.98748826'],
                ['id' => 'a7c40f632a0804ab5.18804076'],
            ],
            $result['body']['data']['countries']
        );
    }

    public function testGetStates(): void
    {
        $result = $this->query('query {
            country (id: "' . self::COUNTRY_WITH_STATES . '") {
                states {
                    id
                    title
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $states = $result['body']['data']['country']['states'];

        $this->assertContains(
            [
                'id'     => 'KY',
                'title'  => 'Kentucky',
            ],
            $states
        );

        $this->assertContains(
            [
                'id'     => 'PA',
                'title'  => 'Pennsylvania',
            ],
            $states
        );
    }

    public function testGetCountriesStates(): void
    {
        $result = $this->query('query {
            countries {
                states {
                    title
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertGreaterThan(
            1,
            $result['body']['data']['countries']
        );

        $this->assertGreaterThan(
            62,
            $result['body']['data']['countries'][0]['states']
        );
    }

    public function testCountryStatesMultilanguage(): void
    {
        $this->setGETRequestParameter('lang', '2');

        $result = $this->query('query {
            country (id: "' . self::COUNTRY_WITH_STATES . '") {
                states {
                    id
                    title
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $states = $result['body']['data']['country']['states'];

        $this->assertContains(
            [
                'id'     => 'AS',
                'title'  => 'Amerikanisch-Samoa',
            ],
            $states
        );

        $this->assertContains(
            [
                'id'     => 'VI',
                'title'  => 'Jungferninseln',
            ],
            $states
        );
    }

    public function testGetCountriesStatesMultilanguage(): void
    {
        $this->setGETRequestParameter('lang', '2');

        $result = $this->query('query {
            countries {
                states {
                    id
                    title
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertGreaterThan(
            1,
            $result['body']['data']['countries']
        );

        $this->assertContains(
            [
                'id'    => 'MP',
                'title' => 'Nördlichen Marianen',
            ],
            $result['body']['data']['countries'][0]['states']
        );
    }
}
