<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Reflection\ExtensionAttributesProcessor;

/**
 * Transform customer object
 */
class CustomerTransformer
{
    /**
     * @var AddressTransformer
     */
    private $addressTransformer;

    /**
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    public function __construct(
        AddressTransformer $addressTransformer,
        ExtensionAttributesProcessor $extensionAttributesProcessor
    ) {
        $this->addressTransformer = $addressTransformer;
        $this->extensionAttributesProcessor = $extensionAttributesProcessor;
    }

    /**
     * Format Customer data into array
     *
     * @param CustomerInterface $customer
     * @return array
     */
    public function toArray(CustomerInterface $customer): array
    {
        return [
            CustomerInterface::ID => $customer->getId(),
            CustomerInterface::DOB => $customer->getDob(),
            CustomerInterface::EMAIL => $customer->getEmail(),
            CustomerInterface::PREFIX => $customer->getPrefix(),
            CustomerInterface::FIRSTNAME => $customer->getFirstname(),
            CustomerInterface::LASTNAME => $customer->getLastname(),
            CustomerInterface::MIDDLENAME => $customer->getMiddlename(),
            CustomerInterface::GENDER => $customer->getGender(),
            CustomerInterface::SUFFIX => $customer->getSuffix(),
            CustomerInterface::TAXVAT => $customer->getTaxvat(),
            CustomerInterface::DEFAULT_BILLING => $customer->getDefaultBilling(),
            CustomerInterface::DEFAULT_SHIPPING => $customer->getDefaultShipping(),
            CustomerInterface::KEY_ADDRESSES => $this->transformAddresses($customer),
            CustomerInterface::EXTENSION_ATTRIBUTES_KEY => $this->transformExtensionAttributes($customer),
        ];
    }

    /**
     * Convert array of address object into array of associative arrays
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function transformAddresses(CustomerInterface $customer): array
    {
        $transformedAddresses = [];
        foreach ($customer->getAddresses() as $address) {
            $transformedAddresses[] = $this->addressTransformer->toArray($address);
        }

        return $transformedAddresses;
    }

    /**
     * Convert extension attributes into array
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function transformExtensionAttributes(CustomerInterface $customer): array
    {
        $extensionAttributes = [];
        if (!empty($customer->getExtensionAttributes())) {
            $extensionAttributes = $this->extensionAttributesProcessor->buildOutputDataArray(
                $customer->getExtensionAttributes(),
                CustomerExtensionInterface::class
            );
        }
        return $extensionAttributes;
    }
}
