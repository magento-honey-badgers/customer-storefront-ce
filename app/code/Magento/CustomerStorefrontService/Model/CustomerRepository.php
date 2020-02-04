<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontService\Model\Data\Customer as CustomerData;
use Magento\CustomerStorefrontService\Model\ResourceModel\Customer;
use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface as CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory as CustomerInterfaceFactory;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var Customer
     */
    private $customerResourceModel;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @param Customer $customerResourceModel
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        Customer $customerResourceModel,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerResourceModel = $customerResourceModel;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        /** @var CustomerData|CustomerInterface $customer */
        $customer = $this->customerFactory->create();
        $this->customerResourceModel->load($customer, $customerId);
        return $customer;
    }
}
