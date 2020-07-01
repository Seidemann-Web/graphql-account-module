<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\NewsletterStatus\Service;

use OxidEsales\Eshop\Application\Model\NewsSubscribed as EshopNewsletterSubscriptionStatusModel;
use OxidEsales\Eshop\Application\Model\User as UserEshopModel;
use OxidEsales\GraphQL\Account\Account\Service\Customer;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\NewsletterStatus\DataType\NewsletterStatusSubscribe as NewsletterStatusSubscribeType;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\EmailEmpty;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\IncorrectSalutation;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NameEmpty;
use OxidEsales\GraphQL\Account\NewsletterStatus\Exception\NewsletterStatusNotFound;
use OxidEsales\GraphQL\Base\Service\Authentication;

use TheCodingMachine\GraphQLite\Annotations\Factory;

final class NewsletterSubscribeInput
{
    private $correctSalutations = ['mr', 'mrs'];

    /** @var Authentication */
    private $authentication;

    /** @var Customer */
    private $customer;

    public function __construct(
        Authentication $authentication,
        Customer $customer
    ) {
        $this->authentication = $authentication;
        $this->customer = $customer;
    }

    /**
     * @Factory
     */
    public function fromUserInput(string $firstName = '', string $lastName = '', string $salutation = '', string $email = ''): NewsletterStatusSubscribeType
    {

        if ($this->authentication->isLogged()) {
            $newsletterStatusModel = $this->handleLoggedInUser($this->authentication->getUserId());
        } else {
            $newsletterStatusModel = $this->handleUserWithoutToken($firstName, $lastName, $salutation, $email);
        }

        return new NewsletterStatusSubscribeType($newsletterStatusModel);
    }

    private function handleLoggedInUser(string $userId): EshopNewsletterSubscriptionStatusModel
    {
        /** @var EshopNewsletterSubscriptionStatusModel $newsletterStatusModel */
        $newsletterStatusModel = oxNew(NewsletterStatusSubscribeType::getModelClass());

        if (!$newsletterStatusModel->loadFromUserId($userId)) {
            throw NewsletterStatusNotFound::byUserId($userId);
        }

        return $newsletterStatusModel;
    }

    private function handleUserWithoutToken(string $firstName = '', string $lastName = '', string $salutation = '', string $email = ''): EshopNewsletterSubscriptionStatusModel
    {
        $this->assertFirstNameNotEmpty($firstName);
        $this->assertLastNameNotEmpty($lastName);
        $this->assertCorrectSalutation($salutation);
        $this->assertEmailNotEmpty($email);

        /** @var EshopNewsletterSubscriptionStatusModel $newsletterStatusModel */
        $newsletterStatusModel = oxNew(NewsletterStatusSubscribeType::getModelClass());

        if (!$newsletterStatusModel->loadFromEmail(($email))) {
            //todo call loadFromEmail once.
            $this->createNewUser($firstName, $lastName, $salutation, $email);
            $newsletterStatusModel->loadFromEmail(($email));
        }
        //if not try loading new one using user object.
        $newsletterStatusModel->save();

        return $newsletterStatusModel;
    }

    private function createNewUser(string $firstName = '', string $lastName = '', string $salutation = '', string $email = '')
    {
        //todo move to infrastrucure
        /** @var UserEshopModel */
        $model = oxNew(UserEshopModel::class);
        $model->assign([
            'OXFNAME'     => $firstName,
            'OXLNAME' => $lastName,
            'OXSAL'   => $salutation,
            'OXUSERNAME'   => $email,
        ]);

        $customerDataType = new CustomerDataType($model);
        $this->customer->create($customerDataType);
    }

    private function assertFirstNameNotEmpty($firstName): bool
    {
        if (!strlen($firstName)) {
            throw new NameEmpty('First name is empty');
        }

        return true;
    }

    private function assertLastNameNotEmpty($lastName): bool
    {
        if (!strlen($lastName)) {
            throw new NameEmpty('Last name is empty');
        }

        return true;
    }

    private function assertCorrectSalutation($salutation): bool
    {
        if (!strlen($salutation)) {
            throw new IncorrectSalutation('Salutation is empty');
        } elseif (!in_array($salutation, $this->correctSalutations)) {
            throw new IncorrectSalutation();
        }

        return true;
    }

    private function assertEmailNotEmpty(string $email): bool
    {
        if (!strlen($email)) {
            throw new EmailEmpty();
        }

        return true;
    }
}
