<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontService\Model\Data\CustomerDocumentFactory;
use Magento\CustomerStorefrontService\Model\ResourceModel\CustomerDocument as CustomerDocumentResource;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;

/**
 * Customer repository
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var CustomerDocumentResource
     */
    private $customerDocumentResource;

    /**
     * @var CustomerDocumentFactory
     */
    private $customerDocumentFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDtoFactory;

    /**
     * @param CustomerDocumentResource $customerDocumentResource
     * @param CustomerDocumentFactory $customerDocumentFactory
     * @param CustomerInterfaceFactory $customerDtoFactory
     */
    public function __construct(
        CustomerDocumentResource $customerDocumentResource,
        CustomerDocumentFactory $customerDocumentFactory,
        CustomerInterfaceFactory $customerDtoFactory
    ) {
        $this->customerDocumentResource = $customerDocumentResource;
        $this->customerDocumentFactory = $customerDocumentFactory;
        $this->customerDtoFactory = $customerDtoFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        /** @var \Magento\CustomerStorefrontService\Model\Data\CustomerDocument $customerDocument */
        $customerDocument = $this->customerDocumentFactory->create();
        $this->customerDocumentResource->load($customerDocument, $customerId);
        return $customerDocument->getCustomerModel();
    }

    public function save(CustomerInterface $customer): CustomerInterface
    {
        /** @var \Magento\CustomerStorefrontService\Model\Data\CustomerDocument $customerDocument */
        $customerDocument = $this->customerDocumentFactory->create([
            'data' => [
                'customer_document' => $customer,
            ]
        ]);
        $customerDocument->setHasDataChanges(true);
        $this->customerDocumentResource->save($customerDocument);
        $savedCustomer = $this->customerDtoFactory->create(['data' => ['website_id' => $customer->getWebsiteId()]]);
        $this->customerDocumentResource->loadByField($customerDocument, $customer->getEmail(), CustomerInterface::EMAIL);

        return $savedCustomer;
    }
}
