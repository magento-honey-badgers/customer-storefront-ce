<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$encryptor = $objectManager->get(EncryptorInterface::class);
/** @var Customer $customer */
$customer = $objectManager->create(Customer::class);

$passwordHash = $encryptor->getHash('password', true);
$customer->setWebsiteId(1)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setTaxvat('12')
    ->setGender(0)
    ->setId(1);
// isObjectNew only works on Customer model
$customer->isObjectNew(true);
// save the customer model - so that model can be first saved and then retrieved from repository
//
$customer->save();
// retrieve the saved customer from the repository as CustomerInterface
/** @var CustomerInterface $savedCustomer */
$savedCustomer = $customerRepository->get('customer@example.com',1);
//save the customer in the repository back to trigger save plugins
/** @var Customer $savedCustomer */
$savedCustomer1 = $customerRepository->save($savedCustomer);
$customerRegistry->remove($savedCustomer1->getId());
