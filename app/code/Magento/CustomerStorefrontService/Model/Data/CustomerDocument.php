<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * CustomerDocument DTO class
 */
class CustomerDocument extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\CustomerStorefrontService\Model\ResourceModel\CustomerDocument::class);
    }

    /**
     * @return string|null
     */
    public function getCustomerRowId(): ?string
    {
        return $this->getData('customer_row_id');
    }

    /**
     * Get Customer model
     *
     * @return CustomerInterface|null
     */
    public function getCustomerModel(): ?CustomerInterface
    {
        return $this->getData('customer_document');
    }
}
