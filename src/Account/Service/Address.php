<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Account\Service;

use OxidEsales\GraphQL\Account\Account\DataType\AddressFilterList;
use OxidEsales\GraphQL\Account\Account\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Account\DataType\InvoiceAddress;
use OxidEsales\GraphQL\Account\Account\Exception\DeliveryAddressNotFound;
use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Address
{
    /** @var Repository */
    private $repository;

    /** @var Authentication */
    private $authenticationService;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authentication $authenticationService,
        Authorization $authorizationService
    ) {
        $this->repository                 = $repository;
        $this->authenticationService      = $authenticationService;
        $this->authorizationService       = $authorizationService;
    }

    /**
     * @return DeliveryAddress[]
     */
    public function customerDeliveryAddresses(AddressFilterList $filterList): array
    {
        return $this->repository->getByFilter(
            $filterList->withUserFilter(
                new StringFilter(
                    $this->authenticationService->getUserId()
                )
            ),
            DeliveryAddress::class
        );
    }

    /**
     * @throws InvalidLogin
     * @throws DeliveryAddressNotFound
     */
    public function delete(string $id): bool
    {
        $deliveryAddress = $this->getDeliveryAddress($id);

        //we got this far, we have a user
        //user can delete only its own delivery address, admin can delete any delivery address
        if (
            $this->authorizationService->isAllowed('DELETE_DELIVERY_ADDRESS')
            || $this->isSameUser($deliveryAddress)
        ) {
            return $this->repository->delete($deliveryAddress->getEshopModel());
        }

        throw new InvalidLogin('Unauthorized');
    }

    public function updateInvoiceAddress(InvoiceAddress $invoiceAddress): InvoiceAddress
    {
        if (!$id = (string) $this->authenticationService->getUserId()) {
            throw new InvalidLogin('Unauthorized');
        }

        $this->repository->saveModel($invoiceAddress->getEshopModel());

        return $invoiceAddress;
    }

    /**
     * @throws DeliveryAddressNotFound
     * @throws InvalidLogin
     */
    private function getDeliveryAddress(string $id): DeliveryAddress
    {
        /** Only logged in users can query delivery addresses */
        if (!$this->authenticationService->isLogged()) {
            throw new InvalidLogin('Unauthenticated');
        }

        try {
            /** @var DeliveryAddress $deliveryAddress */
            $deliveryAddress = $this->repository->getById(
                $id,
                DeliveryAddress::class,
                false
            );
        } catch (NotFound $e) {
            throw DeliveryAddressNotFound::byId($id);
        }

        return $deliveryAddress;
    }

    private function isSameUser(DeliveryAddress $deliveryAddress): bool
    {
        return (string) $deliveryAddress->userId() === (string) $this->authenticationService->getUserId();
    }
}
