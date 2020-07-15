<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\Eshop\Application\Model\Address as EshopAddressModel;
use OxidEsales\Eshop\Application\Model\RequiredAddressFields;
use OxidEsales\Eshop\Application\Model\RequiredFieldsValidator;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress as DeliveryAddressDataType;
use OxidEsales\GraphQL\Account\Account\Exception\DeliveryAddressMissingFields;
use TheCodingMachine\GraphQLite\Types\ID;

final class AddressFactory
{
    public function createValidAddressType(
        string $userid,
        ?string $salutation = null,
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $company = null,
        ?string $additionalInfo = null,
        ?string $street = null,
        ?string $streetNumber = null,
        ?string $zipCode = null,
        ?string $city = null,
        ?ID $countryId = null,
        ?string $phone = null,
        ?string $fax = null
    ): DeliveryAddressDataType {
        /** @var EshopAddressModel */
        $address = oxNew(EshopAddressModel::class);
        $address->assign([
            'oxsal'       => $salutation,
            'oxuserid'    => $userid,
            'oxfname'     => $firstname,
            'oxlname'     => $lastname,
            'oxcompany'   => $company,
            'oxaddinfo'   => $additionalInfo,
            'oxstreet'    => $street,
            'oxstreetnr'  => $streetNumber,
            'oxzip'       => $zipCode,
            'oxcity'      => $city,
            'oxcountryid' => (string) $countryId,
            'oxfon'       => $phone,
            'oxfax'       => $fax,
        ]);

        /** @var RequiredFieldsValidator */
        $validator = oxNew(RequiredFieldsValidator::class);
        /** @var RequiredAddressFields */
        $requiredAddressFields = oxNew(RequiredAddressFields::class);
        $validator->setRequiredFields(
            $requiredAddressFields->getDeliveryFields()
        );

        if (!$validator->validateFields($address)) {
            $invalidFields = array_map(
                function ($v) {
                    return str_replace('oxaddress__ox', '', $v);
                },
                $validator->getInvalidFields()
            );

            throw DeliveryAddressMissingFields::byFields($invalidFields);
        }

        return new DeliveryAddressDataType(
            $address
        );
    }
}
