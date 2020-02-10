<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use Magento\CustomerStorefrontServiceApi\Api\AddressRepositoryInterface;
use Magento\CustomerStorefrontService\Model\Data\AddressDocumentFactory;

/**
 * Address repository.
 */
class AddressRepository implements AddressRepositoryInterface
{
    /**
     * Save customer address.
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(AddressInterface $address): AddressInterface
    {
    }

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $addressId): bool
    {
    }

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $addressId): AddressInterface
    {
    }

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param int $customerId
     * @return \Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(int $customerId): array
    {
    }

    /**
     * Delete customer address.
     *
     * @param AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(AddressInterface $address): bool
    {
    }
}
