<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * AddressDocument DTO class
 */
class AddressDocument extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\CustomerStorefrontService\Model\ResourceModel\AddressDocument::class);
    }

    /**
     * Get CustomerAddress Row ID
     *
     * @return string|null
     */
    public function getCustomerAddressRowId(): ?string
    {
        return $this->getData('customer_address_row_id');
    }

    /**
     * Get Customer Address Model
     *
     * @return AddressInterface|null
     */
    public function getAddressModel(): ?AddressInterface
    {
        return $this->getData('customer_address_document');
    }
}
