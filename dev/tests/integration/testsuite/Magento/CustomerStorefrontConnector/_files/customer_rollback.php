<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);

/** @var \Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface $customerStorefrontRepository */
$customerStorefrontRepository = $objectManager->get(\Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface::class);

/** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
$customer = $customerRepository->get('customer@example.com', 1);
$customerId = $customer->getId();
$customerRepository->delete($customer);

/** @var \Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface $storefrontCustomer */
$storefrontCustomer = $customerStorefrontRepository->getById($customerId);
$customerStorefrontRepository->delete($storefrontCustomer);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
