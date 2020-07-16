<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Infrastructure;

use OxidEsales\Eshop\Application\Model\Country as EshopCountryModel;
use OxidEsales\Eshop\Application\Model\RequiredAddressFields;
use OxidEsales\Eshop\Application\Model\RequiredFieldsValidator;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress as InvoiceAddressDataType;
use OxidEsales\GraphQL\Account\Account\Exception\InvoiceAddressMissingFields;
use TheCodingMachine\GraphQLite\Types\ID;

final class InvoiceAddressFactory
{
    public function createValidInvoiceAddressType(
        CustomerDataType $customerDataType,
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
        ?string $vatID = null,
        ?string $phone = null,
        ?string $mobile = null,
        ?string $fax = null
    ): InvoiceAddressDataType {
        /** @var EshopUserModel $customer */
        $customer = $customerDataType->getEshopModel();

        $customer->assign(
            [
                'oxsal'       => $salutation,
                'oxfname'     => $firstname,
                'oxlname'     => $lastname,
                'oxcompany'   => $company ?: $customer->getFieldData('oxcompany'),
                'oxaddinfo'   => $additionalInfo ?: $customer->getFieldData('oxaddinfo'),
                'oxstreet'    => $street,
                'oxstreetnr'  => $streetNumber,
                'oxzip'       => $zipCode,
                'oxcity'      => $city,
                'oxcountryid' => $countryId,
                'oxustid'     => $vatID ?: $customer->getFieldData('oxustid'),
                'oxprivphone' => $phone ?: $customer->getFieldData('oxprivphone'),
                'oxmobfone'   => $mobile ?: $customer->getFieldData('oxmobfone'),
                'oxfax'       => $fax ?: $customer->getFieldData('oxfax'),
            ]
        );

        /** @var RequiredFieldsValidator */
        $validator = oxNew(RequiredFieldsValidator::class);

        /** @var RequiredAddressFields */
        $requiredAddressFields = oxNew(RequiredAddressFields::class);
        $requiredFields        = $requiredAddressFields->getBillingFields();
        $validator->setRequiredFields(
            $requiredFields
        );

        if (in_array('oxaddress__oxcountryid', $requiredFields, true)) {
            /** @var EshopCountryModel */
            $country = oxNew(EshopCountryModel::class);

            if (!$country->load((string) $countryId)) {
                $customer->assign([
                    'oxcountryid' => null,
                ]);
            }
        }

        if (!$validator->validateFields($customer)) {
            $invalidFields = array_map(
                function ($v) {
                    return str_replace('oxuser__ox', '', $v);
                },
                $validator->getInvalidFields()
            );

            throw InvoiceAddressMissingFields::byFields($invalidFields);
        }

        return new InvoiceAddressDataType($customer);
    }
}
