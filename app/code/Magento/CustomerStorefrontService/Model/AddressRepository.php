<?php
/**
 * Customer address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\Customer\Model\Address as CustomerAddressModel;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\CustomerStorefrontServiceApi\Api\AddressRepositoryInterface;
use Magento\CustomerStorefrontService\Model\Data\AddressDocumentFactory;
use Magento\CustomerStorefrontService\Model\ResourceModel\CustomerDocument as CustomerDocumentResource;
use Magento\CustomerStorefrontService\Model\ResourceModel\AddressDocument as AddressDocumentResource;

/**
 * Address repository.
 */
class AddressRepository implements AddressRepositoryInterface
{
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var AddressDocumentFactory
     */
    protected $addressDocumentFactory;

    /**
     * @var CustomerDocumentResource
     */
    private $customerDocumentResource;

    /**
     * @var AddressDocumentResource
     */
    private $addressDocumentResource;

    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address
     */
    protected $addressResourceModel;

    /**
     * @var \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory
     */
    protected $addressSearchResultsFactory;

    /**
     * @var \Magento\CustomerStorefrontService\Model\ResourceModel\Address\CollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param AddressDocumentFactory $addressDocumentFactory
     * @param CustomerDocumentResource $customerDocumentResource
     * @param AddressDocumentResource $addressDocumentResource
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\Address $addressResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
     * @param \Magento\CustomerStorefrontService\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        AddressDocumentFactory $addressDocumentFactory,
        CustomerDocumentResource $customerDocumentResource,
        AddressDocumentResource $addressDocumentResource,
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\ResourceModel\Address $addressResourceModel,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory,
        \Magento\CustomerStorefrontService\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->addressDocumentFactory = $addressDocumentFactory;
        $this->customerDocumentResource = $customerDocumentResource;
        $this->addressDocumentResource = $addressDocumentResource;
        $this->addressRegistry = $addressRegistry;
        $this->customerRegistry = $customerRegistry;
        $this->addressResourceModel = $addressResourceModel;
        $this->directoryData = $directoryData;
        $this->addressSearchResultsFactory = $addressSearchResultsFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save customer address.
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(AddressInterface $address): AddressInterface
    {
        // TODO: save
        $addressModel = null;
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        if ($address->getId()) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
        }

        if ($addressModel === null) {
            /** @var \Magento\Customer\Model\Address $addressModel */
            $addressModel = $this->addressDocumentFactory->create();
            $addressModel->updateData($address);
            $addressModel->setCustomer($customerModel);
        } else {
            $addressModel->updateData($address);
        }
        $addressModel->setStoreId($customerModel->getStoreId());

        $errors = $addressModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
        $addressModel->save();
        $address->setId($addressModel->getId());
        // Clean up the customer registry since the Address save has a
        // side effect on customer : \Magento\Customer\Model\ResourceModel\Address::_afterSave
        $this->addressRegistry->push($addressModel);
        $this->updateAddressCollection($customerModel, $addressModel);

        return $addressModel->getDataModel();
    }

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId): bool
    {
        //TODO: delete by ID
        $address = $this->addressRegistry->retrieve($addressId);
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        $customerModel->getAddressesCollection()->removeItemByKey($addressId);
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $addressId): AddressInterface
    {
        /** @var \Magento\CustomerStorefrontService\Model\Data\AddressDocument $addressDocument */
        $addressDocument = $this->addressDocumentFactory->create();
        $this->addressDocumentResource->load($addressDocument, $addressId);
        return $addressDocument->getAddressModel();
    }

    /**
     * Update address collection.
     *
     * @param Customer $customer
     * @param Address $address
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateAddressCollection(CustomerModel $customer, CustomerAddressModel $address)
    {
        $customer->getAddressesCollection()->removeItemByKey($address->getId());
        $customer->getAddressesCollection()->addItem($address);
    }

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param int $customerId
     * @return \Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(int $customerId): array
    {
        /** @var \Magento\CustomerStorefrontService\Model\ResourceModel\Address\Collection $collection */
        $collection = $this->addressCollectionFactory->create(['customerId' => $customerId]);

        // TODO: extension attributes
        //  $this->extensionAttributesJoinProcessor->process(
        //      $collection,
        //      AddressInterface::class
        //  );

        $addresses = [];
        /** @var \Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface $address */
        foreach ($collection->getItems() as $address) {
            $addresses[] = $this->getById($address->getId());
        }

        return $addresses;
    }

    /**
     * Delete customer address.
     *
     * @param AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(AddressInterface $address)
    {
        //TODO: delete
        $addressId = $address->getId();
        $address = $this->addressRegistry->retrieve($addressId);
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        $customerModel->getAddressesCollection()->clear();
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }
}
