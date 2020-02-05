<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerStorefrontApi\Api;

/**
 * Customer address CRUD interface.
 */
interface AddressRepositoryInterface
{
    /**
     * Save customer address.
     *
     * @param \Magento\CustomerStorefrontApi\Api\Data\AddressInterface $address
     * @return \Magento\CustomerStorefrontApi\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\CustomerStorefrontApi\Api\Data\AddressInterface $address);

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return \Magento\CustomerStorefrontApi\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId);

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId);
}

