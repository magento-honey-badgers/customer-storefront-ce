<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerDocument extends AbstractModel
{
    /**
     * @return string|null
     */
    public function getStorefrontCustomerId(): ?string
    {
        return $this->getData('storefront_customer_id');
    }

    /**
     * Get default shipping address id
     *
     * @return Customer|null
     */
    public function getCustomerDocument(): ?Customer
    {
        return $this->getData('customer_document');
    }
}
