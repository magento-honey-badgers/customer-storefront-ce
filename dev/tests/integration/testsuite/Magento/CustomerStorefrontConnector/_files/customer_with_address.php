<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/customer.php';

$objectManager = Bootstrap::getObjectManager();

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
/** @var CustomerInterface $customer */
$customer = $customerRepository->get('customer@example.com', 1);
/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(AddressRepositoryInterface::class);
/** @var AddressInterfaceFactory $addressFactory */
$addressFactory = $objectManager->get(AddressInterfaceFactory::class);

$customerAddress = $addressFactory->create([
    'data' => [
        'attribute_set_id' => 2,
        'telephone' => '5127779999',
        'postcode' => 77777,
        'country_id' => 'US',
        'city' => 'CityM',
        'company' => 'CompanyName',
        'street' => 'Green str, 67',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => 1,
        'region_id' => 1,
        'customer_id' => $customer->getId()
    ]
]);

$savedAddress = $addressRepository->save($customerAddress);

$customer->setDefaultBilling($savedAddress->getId());
$customer->setDefaultShipping($savedAddress->getId());
$customer->setAddresses([$savedAddress]);

$customerRepository->save($customer);
