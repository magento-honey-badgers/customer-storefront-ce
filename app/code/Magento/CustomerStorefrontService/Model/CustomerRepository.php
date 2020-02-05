<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerStorefrontService\Model\Data\CustomerDocumentFactory as CustomerDocumentFactory;
use Magento\CustomerStorefrontService\Model\ResourceModel\Customer;

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
     * @param Customer $customerResourceModel
     * @param CustomerDocumentFactory $customerDocumentFactory
     */
    public function __construct(
        Customer $customerResourceModel,
        CustomerDocumentFactory $customerDocumentFactory
    ) {
        $this->customerResourceModel = $customerResourceModel;
        $this->customerDocumentFactory = $customerDocumentFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        /** @var \Magento\CustomerStorefrontService\Model\Data\CustomerDocument $customerDocument */
        $customerDocument = $this->customerDocumentFactory->create();
        $this->customerResourceModel->load($customerDocument, $customerId);
        return $customerDocument->getCustomerModel();
    }
}
