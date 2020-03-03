<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CustomerStorefrontService\Model\CustomerRepository;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
$customerRepository = $objectManager->get(CustomerRepository::class);

$customerData = [
    'website_id' => 1,
    'id' => 99,
    'dob' => '01-01-1985',
    'email' => 'test.customer@example.com',
    'password' => 'password123',
    'group_id' => 1,
    'store_id' => 1,
    'is_active' => 1,
    'prefix' => 'Ms.',
    'firstname' => 'Jane',
    'lastname' => 'Smith',
    'taxvat' => '12',
    'gender' => 1
];

$customer = $customerFactory->create(['data' => $customerData]);
$customerRepository->save($customer);
