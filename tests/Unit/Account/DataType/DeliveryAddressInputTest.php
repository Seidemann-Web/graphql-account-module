<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Unit\Account\DataType;

use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Account\Service\DeliveryAddressInput;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @covers \OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress
 */
final class DeliveryAddressInputTest extends TestCase
{
    public function testEmptyDeliveryAddressInput(): void
    {
        $inputFields = [
            'salutation'     => null,
            'firstname'      => null,
            'lastname'       => null,
            'company'        => null,
            'additionalInfo' => null,
            'street'         => null,
            'streetNumber'   => null,
            'zipCode'        => null,
            'city'           => null,
            'countryId'      => null,
            'phone'          => null,
            'fax'            => null,
        ];

        $input    = new DeliveryAddressInput();
        $dataType = $input->fromUserInput(...array_values($inputFields));

        $this->assertInstanceOf(
            DeliveryAddressDataType::class,
            $dataType
        );

        unset($inputFields['countryId']);
        $fields = array_keys($inputFields);

        foreach ($fields as $field) {
            $this->assertThat($dataType->$field(), $this->isType(IsType::TYPE_STRING));
        }
        $this->assertThat($dataType->countryId(), $this->IsInstanceOf(ID::class));
    }

    public function testDeliveryAddressValidInput(): void
    {
        $inputFields =  [
            'salutation'     => 'MR',
            'firstname'      => 'Marc',
            'lastname'       => 'Muster',
            'company'        => 'No GmbH',
            'additionalInfo' => 'private delivery',
            'street'         => 'Bertoldstrasse',
            'streetNumber'   => '48',
            'zipCode'        => '79098',
            'city'           => 'Freiburg',
            'countryId'      => new ID('a7c40f631fc920687.20179984'),
            'phone'          => '1234',
            'fax'            => '4321',
        ];

        $input    = new DeliveryAddressInput();
        $dataType = $input->fromUserInput(...array_values($inputFields));

        $this->assertInstanceOf(
            DeliveryAddressDataType::class,
            $dataType
        );

        foreach ($inputFields as $key => $field) {
            $this->assertSame($field, $dataType->$key());
        }
    }

    public function testDeliveryAddressMissingInput(): void
    {
        $inputFields =  [
            'salutation'     => 'MR',
            'firstname'      => 'Marc',
            'lastname'       => 'Muster',
            'company'        => 'No GmbH',
            'additionalInfo' => 'private delivery',
            'street'         => 'Bertoldstrasse',
            'streetNumber'   => '48',
            'zipCode'        => '79098',
            'city'           => 'Freiburg',
            'countryId'      => new ID('a7c40f631fc920687.20179984'),
            'phone'          => '1234',
            'fax'            => '4321',
        ];

        $input    = new DeliveryAddressInput();
        $dataType = $input->fromUserInput(...array_values($inputFields));

        $this->assertInstanceOf(
            DeliveryAddressDataType::class,
            $dataType
        );

        foreach ($inputFields as $key => $field) {
            $this->assertSame($field, $dataType->$key());
        }
    }
}
