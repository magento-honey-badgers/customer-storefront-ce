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
use Magento\CustomerStorefrontService\Model\Data\AddressDocumentFactory;
use Magento\CustomerStorefrontService\Model\ResourceModel\CustomerDocument as CustomerResourceModel;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;

/**
 * Customer repository
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var CustomerResourceModel
     */
    private $customerResourceModel;

    /**
     * @var CustomerDocumentFactory
     */
    private $customerDocumentFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\CustomerStorefrontService\Model\ResourceModel\AddressDocument
     */
    private $addressResourceModel;

    /**
     * @var AddressDocumentFactory
     */
    private $addressDocumentFactory;

    /**
     * @param CustomerResourceModel $customerResourceModel
     * @param CustomerDocumentFactory $customerDocumentFactory
     * @param AddressDocumentFactory $addressDocumentFactory
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CustomerResourceModel $customerResourceModel,
        CustomerDocumentFactory $customerDocumentFactory,
        AddressDocumentFactory $addressDocumentFactory,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerResourceModel = $customerResourceModel;
        $this->customerDocumentFactory = $customerDocumentFactory;
        $this->addressDocumentFactory = $addressDocumentFactory;
        //$this->addressResourceModel = $addressResourceModel;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        /** @var \Magento\CustomerStorefrontService\Model\Data\CustomerDocument $customerDocument */
//        $addressDocument = $this->addressDocumentFactory->create();
//        $this->addressResourceModel->load($addressDocument, 32323);
        /** @var \Magento\CustomerStorefrontService\Model\Data\CustomerDocument $customerDocument */
        $customerDocument = $this->customerDocumentFactory->create();
        $this->customerResourceModel->load($customerDocument, $customerId);
        return $customerDocument->getCustomerModel();
    }

    public function save(CustomerInterface $customer): CustomerInterface
    {
        $customerDocument = $this->customerDocumentFactory->create([
            'data' => [
                'customer_document' => $customer,
//                'customer_model' => $customer
            ]
        ]);
        $customerDocument->setHasDataChanges(true);
        $this->customerResourceModel->save($customerDocument);
        $savedCustomer = $this->customerFactory->create(['data' => ['website_id' => $customer->getWebsiteId()]]);
        $this->customerResourceModel->loadByField($customerDocument, $customer->getEmail(), CustomerInterface::EMAIL);

        return $savedCustomer;
    }
}
