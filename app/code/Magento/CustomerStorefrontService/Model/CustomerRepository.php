<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\Customer\Model\CustomerRegistry;
use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory as CustomerInterfaceFactory;

class CustomerRepository implements CustomerRepositoryInterface
{

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get customer by Customer ID.
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);
        return $customerModel->getDataModel();
    }
}
