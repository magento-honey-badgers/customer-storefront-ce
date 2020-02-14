<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
$encryptor = $objectManager->get(EncryptorInterface::class);

$passwordHash = $encryptor->getHash('password', true);

$customer = $customerFactory->create([
    'data' => [
        'website_id' => 1,
        'dob' => '01-01-1970',
        'email' => 'customer.norollingback@example.com',
        'password' => 'password',
        'group_id' => 1,
        'store_id' => 1,
        'is_active' => 1,
        'prefix' => 'Mr.',
        'firstname' => 'John',
        'middlename' => 'A',
        'lastname' => 'Smith',
        'suffix' => 'Esq.',
        'taxvat' => '12',
        'gender' => 0
    ]
]);

$customerRepository->save($customer, $passwordHash);
