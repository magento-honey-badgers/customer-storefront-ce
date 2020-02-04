<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\Customer\Model\CustomerRegistry;
use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface as CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory as CustomerInterfaceFactory;
use Magento\CustomerStorefrontService\Model\ResourceModel\Customer;

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
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @param Customer $customerResourceModel
     * @param CustomerRegistry $customerRegistry
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        Customer $customerResourceModel,
        CustomerRegistry $customerRegistry,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerResourceModel = $customerResourceModel;
        $this->customerRegistry = $customerRegistry;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        //$customerModel = $this->customerRegistry->retrieve($customerId);
        //return $customerModel->getDataModel();
        /** @var \Magento\CustomerStorefrontService\Model\Data\Customer|\Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface $customer */
        $customer = $this->customerFactory->create();
        $this->customerResourceModel->load($customer, $customerId);
        return $customer;
    }
}
