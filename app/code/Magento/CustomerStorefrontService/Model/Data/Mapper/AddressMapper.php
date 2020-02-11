<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data\Mapper;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;

/**
 * Maps Address Model
 */
class AddressMapper
{
    private $addressInterfaceFactory;

    /**
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory
    ) {
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
        /**
         * @var $address AddressInterface
         */
        $address = $this->addressInterfaceFactory->create(['data' => $data['data']]);
        return $address;
    }
}
