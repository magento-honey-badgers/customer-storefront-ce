<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\AddressRepositoryInterface as AddressStorefrontRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(AddressRepositoryInterface::class);
/** @var AddressStorefrontRepositoryInterface $addressStorefrontRepository */
$addressStorefrontRepository = $objectManager->get(AddressStorefrontRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('postcode', '77777')->create();

/** @var \Magento\Customer\Api\Data\AddressSearchResultsInterface $addresses */
$addresses = $addressRepository->getList($searchCriteria);
/** @var \Magento\Customer\Api\Data\AddressInterface $address */
foreach ($addresses->getItems() as $address) {
    $addressRepository->delete($address);
}
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
/** @var CustomerInterface $customer */
$customer = $customerRepository->get('customer@example.com', 1);
$customerId = $customer->getId();

//remove from storefront_customer_address table
try{
    /** @var AddressInterface $storefrontAddresses */
    $storefrontAddress = $addressStorefrontRepository->getById((int)$customer->getDefaultBilling());
    //$storefrontAddresse->getId();
    $addressStorefrontRepository->delete($storefrontAddress);

} catch (NoSuchEntityException $e) {
    //address already removed from storefront storage
}

//require __DIR__ . '/customer_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
