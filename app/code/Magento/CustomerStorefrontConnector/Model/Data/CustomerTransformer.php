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
        $customerData['extension_attributes'] = $rawCustomerData['extension_attributes'] ?? [];
        $customerData['custom_attributes'] = $rawCustomerData['custom_attributes'] ?? [];

        return $customerData;
    }
}
