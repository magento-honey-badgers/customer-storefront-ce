<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data\Mapper;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

/**
 * Maps Customer Model
 */
class CustomerMapper
{
    private $customerFactory;

    /**
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
    }

    /**
     * Customer data Mapper
     *
     * @param array $data
     * @return CustomerInterface
     */
    public function mapCustomerData(array $data)
    {
        /**
         * @var $customer CustomerInterface
         */
        $customer = $this->customerFactory->create(['data' => $data['data']]);
        return $customer;
    }
}
