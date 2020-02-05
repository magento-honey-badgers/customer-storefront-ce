<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\Data;

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
     * @var array
     */
    private $basicFields = [
        'id',
        'store_id',
        'website_id',
        'default_billing',
        'default_shipping',
        'dob',
        'email',
        'prefix',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'gender',
        'taxvat'
    ];

    /**
     * @param AddressTransformer $addressTransformer
     */
    public function __construct(
        AddressTransformer $addressTransformer
    ) {
        $this->addressTransformer = $addressTransformer;
    }

    /**
     * Format Customer data into array
     *
     * @param array $rawCustomerData
     * @return array
     */
    public function toArray(array $rawCustomerData): array
    {
        $customerData = [];
        foreach ($this->basicFields as $field) {
            $customerData[$field] = $rawCustomerData[$field] ?? null;
        }
        $customerData['addresses'] = $this->transformAddresses($rawCustomerData['addresses']);
        $customerData['extension_attributes'] = $rawCustomerData['extension_attributes'] ?? [];
        $customerData['custom_attributes'] = $rawCustomerData['custom_attributes'] ?? [];

        return $customerData;
    }

    /**
     * Convert array of address object into array of associative arrays
     *
     * @param array $addresses
     * @return array
     */
    private function transformAddresses(array $addresses): array
    {
        $transformedAddresses = [];
        foreach ($addresses as $address) {
            $transformedAddresses[] = $this->addressTransformer->toArray($address);
        }

        return $transformedAddresses;
    }
}
