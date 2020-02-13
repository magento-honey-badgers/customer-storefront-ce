<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Registry;
use Magento\Framework\MessageQueue\QueueRepository;
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

$queueRepository = $objectManager->get(QueueRepository::class);
/** @var QueueInterface $monolithDeleteQueue */
$monolithDeleteQueue = $queueRepository->get('amqp', 'customer.monolith.connector.customer.delete');
/** @var EnvelopeInterface $monolithDeleteMessage */
$monolithDeleteMessage = $monolithDeleteQueue->dequeue();
$monolithDeleteQueue->acknowledge($monolithDeleteMessage);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
