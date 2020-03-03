<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CustomerStorefrontService\Model\AddressRepository;
use Magento\CustomerStorefrontService\Model\CustomerRepository;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/customer.php';

$objectManager = Bootstrap::getObjectManager();
$customerRepository = $objectManager->get(CustomerRepository::class);
$addressRepository = $objectManager->get(AddressRepository::class);
$customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
$addressFactory = $objectManager->get(AddressInterfaceFactory::class);

$addresses = [
    [
        'id' => 57,
        'customer_id' => 99,
        'country_id' => 'US',
        'street' => ['Green str, 67'],
        'company' => 'CompanyName',
        'telephone' => '5127779999',
        'postcode' => '77777',
        'city' => 'Austin',
        'firstname' => 'Jane',
        'lastname' => 'Smith',
        'prefix' => 'Ms.',
        'default_shipping' => true,
        'region' => [
            'region_code' => 'TX',
            'region' => 'Texas',
            'region_id' => 57
        ]
    ],
    [
        'id' => 58,
        'customer_id' => 99,
        'country_id' => 'CA',
        'street' => ['123 Fake Street'],
        'telephone' => '91234567890',
        'postcode' => 'M1B 5K7',
        'city' => 'Toronto',
        'firstname' => 'Jane',
        'lastname' => 'Smith',
        'default_billing' => true,
        'prefix' => 'Ms.',
        'region' => [
            'region_code' => 'ON',
            'region' => 'Ontario',
            'region_id' => 74
        ]
    ],
];

foreach ($addresses as $addressData) {
    $address = $addressFactory->create(['data' => $addressData]);
    $addressRepository->save($address);
}

$customer = $customerRepository->getById(99);
$customer->setAddresses($addresses);

$customerRepository->save($customer);
