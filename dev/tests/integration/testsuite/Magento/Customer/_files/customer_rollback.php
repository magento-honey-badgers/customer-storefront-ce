<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface as StorefrontCustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface as StorefrontCustomerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);

/** @var StorefrontCustomerRepositoryInterface $customerStorefrontRepository */
$customerStorefrontRepository = $objectManager->get(StorefrontCustomerRepositoryInterface::class);
try {
    /** @var CustomerInterface $customer */
    $customer = $customerRepository->get('customer@example.com', 1);
    $customerId = $customer->getId();
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    // customer already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
