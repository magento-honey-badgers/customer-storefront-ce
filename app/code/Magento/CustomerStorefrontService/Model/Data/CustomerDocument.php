<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validator\Exception as ValidatorException;

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
     * Validate model before saving it
     *
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function validateBeforeSave()
    {
        parent::validateBeforeSave();
        // TODO: use real Zend validator and rules class
        if (!$this->getCustomerModel()->getEmail()) {
            throw new ValidatorException(__('The customer email is missing. Enter and try again.'));
        }
        return $this;
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
