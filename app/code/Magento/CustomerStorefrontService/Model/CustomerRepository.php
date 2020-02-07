<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontService\Model\Storage\Customer as CustomerStorage;
use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface as CustomerInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var CustomerStorage
     */
    private $customerStorage;

    /**
     * @param CustomerStorage $customerStorage
     */
    public function __construct(
        CustomerStorage $customerStorage
    ) {
        $this->customerStorage = $customerStorage;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        return $this->customerStorage->fetchById($customerId);
    }

    /**
     * @inheritDoc
     */
    public function save(CustomerInterface $customer): CustomerInterface
    {
        $this->customerStorage->persist($customer);

        return $customer;
    }

    /**
     * @inheritDoc
     */
    public function delete(CustomerInterface $customer): bool
    {
        return $this->customerStorage->delete($customer);
    }
}
