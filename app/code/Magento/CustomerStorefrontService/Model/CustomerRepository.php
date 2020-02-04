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
     * Get customer by Customer ID.
     *
     * @param int $customerId
     * @return \Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($customerId)
    {
        //$customerModel = $this->customerRegistry->retrieve($customerId);
        //return $customerModel->getDataModel();
        //$customer = $this->customerFactory->create();
        //$this->customerResourceModel->load($customer, $customerId, 'storefront_customer_id');
        //return $customer;
    }
}
