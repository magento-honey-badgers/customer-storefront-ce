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
use Magento\CustomerStorefrontService\Model\Data\CustomerDocumentFactory as CustomerDocumentFactory;
use Magento\CustomerStorefrontService\Model\ResourceModel\Customer;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var Customer
     */
    private $customerResourceModel;

    /**
     * @var CustomerDocumentFactory
     */
    private $customerDocumentFactory;

    /**
     * @var CustomerStorage
     */
    private $customerStorage;

    /**
     * @param Customer $customerResourceModel
     * @param CustomerDocumentFactory $customerDocumentFactory
     * @param CustomerStorage $customerStorage
     */
    public function __construct(
        Customer $customerResourceModel,
        CustomerDocumentFactory $customerDocumentFactory,
        CustomerStorage $customerStorage
    ) {
        $this->customerResourceModel = $customerResourceModel;
        $this->customerDocumentFactory = $customerDocumentFactory;
        $this->customerStorage = $customerStorage;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        /** @var \Magento\CustomerStorefrontService\Model\Data\CustomerDocument $customerDocument */
        $customerDocument = $this->customerDocumentFactory->create();
        $this->customerResourceModel->load($customerDocument, $customerId);
        return $customerDocument->getCustomerDocument();
    }

    public function save(CustomerInterface $customer): CustomerInterface
    {
        $this->customerStorage->persist($customer);

        return $customer;
    }
}
