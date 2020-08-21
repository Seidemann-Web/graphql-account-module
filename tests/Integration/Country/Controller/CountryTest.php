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
                position
                active
                title
                isoAlpha2
                isoAlpha3
                isoNumeric
                shortDescription
                description
                creationDate
                states {
                    id
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $countryData = $result['body']['data']['country'];

        $this->assertSame('T', substr($countryData['creationDate'], 10, 1));
        unset($countryData['creationDate']);

        $this->assertEquals(
            [
                'id'               => self::ACTIVE_COUNTRY,
                'active'           => true,
                'title'            => 'Deutschland',
                'states'           => [],
                'position'         => 1,
                'isoAlpha2'        => 'DE',
                'isoAlpha3'        => 'DEU',
                'isoNumeric'       => '276',
                'shortDescription' => 'EU1',
                'description'      => '',
            ],
            $countryData
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
        $this->setGETRequestParameter('lang', '1');

        $result = $this->query('query {
            countries {
                title
                position
            }
        }');

        $this->assertResponseStatus(200, $result);

        $countries = $result['body']['data']['countries'];
        $this->assertCount(5, $countries);

        // Test default sorting for countries
        $this->assertEquals(
            [
                ['title' => 'Germany',        'position' => 1],
                ['title' => 'United States',  'position' => 2],
                ['title' => 'Switzerland',    'position' => 3],
                ['title' => 'Austria',        'position' => 4],
                ['title' => 'United Kingdom', 'position' => 5],
            ],
            $countries
        );
    }

    public function testGetCountryListWithPartialFilter(): void
    {
        $this->setGETRequestParameter('lang', '0');

        $result = $this->query('query {
            countries(filter: {
                title: {
                    contains: "sch"
                }
            }) {
                id
                title
                position
            }
        }');

        $this->assertResponseStatus(200, $result);

        $this->assertEquals(
            [
                ['id' => 'a7c40f631fc920687.20179984', 'title' => 'Deutschland', 'position' => 1],
                ['id' => 'a7c40f6321c6f6109.43859248', 'title' => 'Schweiz',     'position' => 3],
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
            countries(sort: {position: "DESC"}) {
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
            countries(sort: {title: "ASC"}) {
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

    public function dataProviderSortedStates()
    {
        return [
            'title_asc'  => [
                'sortquery' => 'ASC',
                'method'    => 'asort',
            ],
            'title_desc' => [
                'sortquery' => 'DESC',
                'method'    => 'arsort',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSortedStates
     */
    public function testGetStatesListWithTitleSorting(string $sort, string $method): void
    {
        $this->setGETRequestParameter('lang', '1');

        $result = $this->query('query {
            country (id: "' . self::COUNTRY_WITH_STATES . '") {
                states(sort: {
                    title: "' . $sort . '"
                }) {
                    id
                    title
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $sortedStates = [];

        foreach ($result['body']['data']['country']['states'] as $state) {
            $sortedStates[$state['id']] = $state['title'];
        }

        $expected = $sortedStates;

        $method($expected, SORT_FLAG_CASE | SORT_STRING);

        $this->assertSame(
            $expected,
            $sortedStates
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
        $this->setGETRequestParameter('lang', '0');

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
        $this->setGETRequestParameter('lang', '0');

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
            $result['body']['data']['countries'][1]['states']
        );
    }
}
