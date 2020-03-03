<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontApi\Api;

use Magento\CustomerStorefrontApi\Api\Data\AddressInterface;
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
    public function deleteById(int $addressId): bool;

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param int $customerId
     * @return \Magento\CustomerStorefrontApi\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(int $customerId): array;

    /**
     * Delete customer address.
     *
     * @param AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(AddressInterface $address): bool;
}
