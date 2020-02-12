<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Customer\Api\Data\AddressInterface;
use Magento\TestFramework\ObjectManager;

ObjectManager::getInstance()->get(Magento\Customer\Api\CustomerRepositoryInterface::class);
/** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
$customer = $this->customerRepository->get('customer@example.com', 1);
$customerId = $customer->getId();

/** @var AddressInterface $customerAddress */
$customerAddress = $this->addressRepository->getById($customer->getDefaultBilling());
$addressId = $customerAddress->getId();

return [
    [
        'id' => "$addressId",
        'customer_id' => "$customerId",
        'country_id' => 'US',
        'street' =>['Green str, 67'],
        'company' => 'CompanyName',
        'telephone' => '5127779999',
        'postcode' => '77777',
        'city' => 'CityM',
        'firstname' =>'Johny',
        'lastname' =>'Smith',
        'middlename' =>'A',
        'prefix' =>'Mr.',
        'suffix' =>'Esq',
        'default_shipping' => true,
        'default_billing' => true,
        'region'=> [
            'region_code' => 'AL',
            'region' => 'Alabama',
            'region_id' => 1
        ],
        'extension_attributes'=>[]
    ]
];
