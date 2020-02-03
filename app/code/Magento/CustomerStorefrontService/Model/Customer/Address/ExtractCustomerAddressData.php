<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Customer\Address;

use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\CustomerFactory;

/**
 * Transform single customer address data from object to in array format
 */
class ExtractCustomerAddressData
{
    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var CustomerResourceModel
     */
    private $customerResourceModel;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     * @param CustomerResourceModel $customerResourceModel
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer,
        CustomerResourceModel $customerResourceModel,
        CustomerFactory $customerFactory
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Curate shipping and billing default options
     *
     * @param array $address
     * @param AddressInterface $addressObject
     * @return array
     */
    private function curateAddressDefaultValues(array $address, AddressInterface $addressObject) : array
    {
        $customerModel = $this->customerFactory->create();
        $this->customerResourceModel->load($customerModel, $addressObject->getCustomerId());
        $address[CustomerInterface::DEFAULT_BILLING] =
            ($customerModel->getDefaultBillingAddress()
                && $addressObject->getId() == $customerModel->getDefaultBillingAddress()->getId());
        $address[CustomerInterface::DEFAULT_SHIPPING] =
            ($customerModel->getDefaultShippingAddress()
                && $addressObject->getId() == $customerModel->getDefaultShippingAddress()->getId());
        return $address;
    }

    /**
     * Transform single customer address data from object to in array format
     *
     * @param AddressInterface $address
     * @return array
     */
    public function execute(AddressInterface $address): array
    {
        $addressData = $this->serviceOutputProcessor->process(
            $address,
            AddressRepositoryInterface::class,
            'getById'
        );
        $addressData = $this->curateAddressDefaultValues($addressData, $address);

        if (isset($addressData[CustomAttributesDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
            $addressData = array_merge(
                $addressData,
                $addressData[CustomAttributesDataInterface::EXTENSION_ATTRIBUTES_KEY]
            );
        }

        $addressData['customer_id'] = null;

        if (isset($addressData['country_id'])) {
            $addressData['country_code'] = $addressData['country_id'];
        }

        return $addressData;
    }
}
