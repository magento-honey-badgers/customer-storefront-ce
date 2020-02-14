<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\Data;

/**
 * Transform Address object
 */
class AddressTransformer
{
    /**
     * @var array
     */
    private $basicFields = [
        'id',
        'customer_id',
        'country_id',
        'street',
        'company',
        'telephone',
        'fax',
        'postcode',
        'city',
        'firstname',
        'lastname',
        'middlename',
        'prefix',
        'suffix',
        'vat_id',
        'default_shipping',
        'default_billing',
        'region'
    ];

    /**
     * Format address data into array
     *
     * @param array $rawAddressData
     * @return array
     */
    public function toArray(array $rawAddressData): array
    {
        $addressData = [];
        foreach ($this->basicFields as $field) {
            $addressData[$field] = $rawAddressData[$field] ?? null;
        }
        $addressData['extension_attributes'] = $rawAddressData['extension_attributes'] ?? [];

        return $addressData;
    }
}
