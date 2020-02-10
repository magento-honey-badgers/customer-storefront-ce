<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontServiceApi\Api;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer CRUD interface.
 */
interface CustomerRepositoryInterface
{
    /**
     * Get customer by Customer ID and Store Id.
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws NoSuchEntityException If customer with the specified ID does not exist.
     * @throws LocalizedException
     */
    public function getById(int $customerId): CustomerInterface;

    /**
     * Save Customer
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function save(CustomerInterface $customer): CustomerInterface;

    /**
     * Delete Customer
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function delete(CustomerInterface $customer): CustomerInterface;
}
