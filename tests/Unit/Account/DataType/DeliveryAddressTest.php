<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Unit\Account\DataType;

use OxidEsales\Eshop\Application\Model\Address as EshopAddressModel;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress
 */
final class DeliveryAddressTest extends TestCase
{
    public function testEmptyDeliveryAddress(): void
    {
        $model    = new DeliveryAddressModelStub();
        $dataType = new DeliveryAddress($model);

        $this->assertInstanceOf(
            EshopAddressModel::class,
            $dataType->getEshopModel()
        );
        $this->assertIsString(
            $dataType->salutation()
        );
        $this->assertIsString(
            $dataType->firstname()
        );
        $this->assertIsString(
            $dataType->lastname()
        );
        $this->assertIsString(
            $dataType->company()
        );
        $this->assertIsString(
            $dataType->additionalInfo()
        );
        $this->assertIsString(
            $dataType->street()
        );
        $this->assertIsString(
            $dataType->streetNumber()
        );
        $this->assertIsString(
            $dataType->zipCode()
        );
        $this->assertIsString(
            $dataType->city()
        );
        $this->assertIsString(
            $dataType->phone()
        );
        $this->assertIsString(
            $dataType->fax()
        );
    }

    public function testEnrichedDeliveryAddress(): void
    {
        $model    = new DeliveryAddressModelStub();
        $data     = [
            'oxsal'        => 'MR',
            'oxfname'      => 'Marc',
            'oxlname'      => 'Muster',
            'oxcompany'    => 'None GmbH',
            'oxaddinfo'    => 'private delivery',
            'oxstreet'     => 'Haupstr.',
            'oxstreetnr'   => '13',
            'oxzip'        => '79098',
            'oxcity'       => 'Freiburg',
            'oxfon'        => '',
            'oxfax'        => '',
        ];
        $model->assign($data);
        $dataType = new DeliveryAddress($model);

        $this->assertInstanceOf(
            EshopAddressModel::class,
            $dataType->getEshopModel()
        );
        $this->assertSame(
            $dataType->salutation(),
            $data['oxsal']
        );
        $this->assertSame(
            $dataType->firstname(),
            $data['oxfname']
        );
        $this->assertSame(
            $dataType->lastname(),
            $data['oxlname']
        );
        $this->assertSame(
            $dataType->company(),
            $data['oxcompany']
        );
        $this->assertSame(
            $dataType->additionalInfo(),
            $data['oxaddinfo']
        );
        $this->assertSame(
            $dataType->street(),
            $data['oxstreet']
        );
        $this->assertSame(
            $dataType->streetNumber(),
            $data['oxstreetnr']
        );
        $this->assertSame(
            $dataType->zipCode(),
            $data['oxzip']
        );
        $this->assertSame(
            $dataType->city(),
            $data['oxcity']
        );
        $this->assertSame(
            $dataType->phone(),
            $data['oxfon']
        );
        $this->assertSame(
            $dataType->fax(),
            $data['oxfax']
        );
    }
}
