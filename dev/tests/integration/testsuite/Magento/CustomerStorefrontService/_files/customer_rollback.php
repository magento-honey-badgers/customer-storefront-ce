<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CustomerStorefrontService\Model\CustomerRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$customerRepository = $objectManager->get(CustomerRepository::class);

$customerId = 99;

try {
    $customer = $customerRepository->getById($customerId);
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    //customer does not exist
}
