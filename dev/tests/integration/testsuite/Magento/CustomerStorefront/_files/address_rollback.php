<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CustomerStorefront\Model\AddressRepository;
use Magento\CustomerStorefrontApi\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$addressRepository = $objectManager->get(AddressRepository::class);
try {
    $address = $addressRepository->getById(57);
    $addressRepository->delete($address);
} catch (NoSuchEntityException $e) {
    //Address does not exist
}
