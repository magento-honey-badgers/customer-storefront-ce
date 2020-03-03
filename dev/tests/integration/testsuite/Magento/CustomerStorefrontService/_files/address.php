<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CustomerStorefrontService\Model\AddressRepository;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$addressRepository = $objectManager->get(AddressRepository::class);
$addressFactory = $objectManager->get(AddressInterfaceFactory::class);

$addressData = [
    'id' => 57,
    'country_id' => 'US',
    'street' => ['Green str, 67'],
    'company' => 'CompanyName',
    'telephone' => '5127779999',
    'postcode' => '77777',
    'city' => 'Austin',
    'firstname' => 'Jane',
    'lastname' => 'Smith',
    'prefix' => 'Ms.',
    'region' => [
        'region_code' => 'TX',
        'region' => 'Texas',
        'region_id' => 57
    ]
];

$address = $addressFactory->create(['data' => $addressData]);
$addressRepository->save($address);
