<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\TestFramework\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;

ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
$customer = $this->customerRepository->get('customer@example.com', 1);
$customerId = $customer->getId();

return [
    [
        'id' => "$customerId",
        'store_id' => 1,
        'website_id' => 1,
        'default_billing' => '0',
        'default_shipping' => '0',
        'dob'=> '01-01-1970',
        'email' =>'customer@example.com',
        'prefix' =>'Mr.',
        'firstname' =>'Johny',
        'middlename' =>'A',
        'lastname' =>'Smith',
        'suffix' =>'Esq',
        'gender' =>0,
        'taxvat' =>'12',
        'addresses'=> [],
        'extension_attributes'=>[
            'is_subscribed'=> false
        ],
        'custom_attributes'=>[]
    ]
];
