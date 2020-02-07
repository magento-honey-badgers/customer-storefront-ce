<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data\Mapper;

use Psr\Log\LoggerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;

/**
 * Maps Address Model
 */
class AddressMapper
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $addressInterfaceFactory;

    /**
     * @param LoggerInterface $logger
     * @param AddressInterfaceFactory $customerFactory
     */
    public function __construct(
        LoggerInterface $logger,
        AddressInterfaceFactory $addressFactory
    ) {
        $this->logger = $logger;
        $this->addressInterfaceFactory = $addressFactory;
    }

    /**
     * Customer data Mapper
     *
     * @param array $data
     * @return AddressInterface
     */
    public function mapAddressData(array $data)
    {
        $this->logger->info("message", $data['data']);
        /**
         * @var $address AddressInterface
         */
        $address = $this->addressInterfaceFactory->create(['data' => $data['data']]);
        $this->logger->info("message", [$address->getCustomerId()]);
        return $address;
    }
}
