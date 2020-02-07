<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontGraphQl\Model\Resolver;

use Magento\CustomerStorefrontService\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\CustomerStorefrontServiceApi\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Customers addresses field resolver
 */
class CustomerAddresses implements ResolverInterface
{
    /**
     * @var ExtractCustomerAddressData
     */
    private $extractCustomerAddressData;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        ExtractCustomerAddressData $extractCustomerAddressData,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->extractCustomerAddressData = $extractCustomerAddressData;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['customer_id'])) {
            throw new LocalizedException(__('"customer_id" value should be specified'));
        }
        $customer_id = $value['customer_id'];

        $addresses = $this->addressRepository->getList($customer_id);

        $addressesData = [];
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $addressesData[] = $address->__toArray();
            }
        }
        return $addressesData;
    }
}
