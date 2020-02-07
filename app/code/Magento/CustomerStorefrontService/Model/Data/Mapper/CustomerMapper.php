<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data\Mapper;

use Psr\Log\LoggerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

/**
 * Maps Customer Model
 */
class CustomerMapper
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $customerFactory;

    /**
     * @param LoggerInterface $logger
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->logger = $logger;
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
        $this->logger->info("dfs", $data['data']);
        /**
         * @var $customer CustomerInterface
         */
        $customer = $this->customerFactory->create(['data' => $data['data']]);
        $this->logger->info($customer->getFirstname());
        return $customer;
    }
}
