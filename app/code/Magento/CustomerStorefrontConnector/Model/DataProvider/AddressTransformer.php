<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\Data\AddressExtensionInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Reflection\ExtensionAttributesProcessor;

/**
 * Transform Address object
 */
class AddressTransformer
{
    /**
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    /**
     * @param ExtensionAttributesProcessor $extensionAttributesProcessor
     */
    public function __construct(ExtensionAttributesProcessor $extensionAttributesProcessor)
    {
        $this->extensionAttributesProcessor = $extensionAttributesProcessor;
    }

    /**
     * Format address data into array
     *
     * @param AddressInterface $address
     * @return array
     */
    public function toArray(AddressInterface $address)
    {
        return [
            AddressInterface::ID => $address->getId(),
            AddressInterface::CUSTOMER_ID => $address->getCustomerId(),
            AddressInterface::REGION => [
                RegionInterface::REGION => $address->getRegion()->getRegion(),
                RegionInterface::REGION_ID => $address->getRegion()->getRegionId(),
                RegionInterface::REGION_CODE => $address->getRegion()->getRegionCode()
            ],
            AddressInterface::COUNTRY_ID => $address->getCountryId(),
            AddressInterface::STREET => $address->getStreet(),
            AddressInterface::COMPANY => $address->getCompany(),
            AddressInterface::TELEPHONE => $address->getTelephone(),
            AddressInterface::FAX => $address->getFax(),
            AddressInterface::POSTCODE => $address->getPostcode(),
            AddressInterface::CITY => $address->getCity(),
            AddressInterface::FIRSTNAME => $address->getFirstname(),
            AddressInterface::LASTNAME => $address->getLastname(),
            AddressInterface::MIDDLENAME => $address->getMiddlename(),
            AddressInterface::PREFIX => $address->getPrefix(),
            AddressInterface::SUFFIX => $address->getSuffix(),
            AddressInterface::VAT_ID => $address->getVatId(),
            AddressInterface::DEFAULT_BILLING => $address->isDefaultBilling(),
            AddressInterface::DEFAULT_SHIPPING => $address->isDefaultShipping(),
            AddressInterface::EXTENSION_ATTRIBUTES_KEY => $this->transformExtensionAttributes($address)
        ];
    }

    /**
     * Format extension attributes into array
     *
     * @param AddressInterface $address
     * @return array
     */
    private function transformExtensionAttributes(AddressInterface $address): array
    {
        $extensionAttributes = [];
        if (!empty($address->getExtensionAttributes())) {
            $extensionAttributes = $this->extensionAttributesProcessor->buildOutputDataArray(
                $address->getExtensionAttributes(),
                AddressExtensionInterface::class
            );
        }
        return $extensionAttributes;
    }
}
