<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
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
        return $this->getData('customer_row_id');
    }

    /**
     * Get default shipping address id
     *
     * @return Customer|null
     */
    public function getCustomerModel(): ?CustomerInterface
    {
        return $this->getData('customer_document');
    }
}
