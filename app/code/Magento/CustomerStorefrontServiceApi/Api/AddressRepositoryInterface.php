<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerStorefrontServiceApi\Api;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer address CRUD interface.
 */
interface AddressRepositoryInterface
{
    /**
     * Save customer address.
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws LocalizedException
     */
    public function save(AddressInterface $address): AddressInterface;

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return AddressInterface
     * @throws LocalizedException
     */
    public function getById(int $addressId): AddressInterface;

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($addressId): bool;
}

