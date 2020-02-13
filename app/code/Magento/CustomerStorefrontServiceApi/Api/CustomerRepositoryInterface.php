<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontServiceApi\Api;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

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
     * Save customer
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     */
    public function save(CustomerInterface $customer): CustomerInterface;

    /**
     * Delete customer
     *
     * @param CustomerInterface $customer
     * @return bool
     * @throws LocalizedException
     */
    public function delete(CustomerInterface $customer): bool;

    /**
     * Delete customer by Id
     *
     * @param int $customerId
     * @return bool
     * @throws LocalizedException
     */
    public function deleteById(int $customerId): bool;
}
