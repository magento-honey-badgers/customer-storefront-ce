<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\CustomerAddressStorefrontPublisherPlugin;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Plugin class to to delete customer address and invoke consumers
 *
 * After customer address is deleted, consumers are started to clean up the queues on both monolith and storefront side
 */
class CustomerAddressAfterSaveAndAfterDelete extends CustomerAddressStorefrontPublisherPlugin
{
    /**
     * @inheritDoc
     *
     * force run the customer address delete consumers after the fixture rollback
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param bool $result
     * @param AddressInterface $address
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDelete(
        AddressRepositoryInterface $addressRepository,
        $result,
        AddressInterface $address
    ) {
        $deleteAddressConsumers = [
            'customer.monolith.connector.address.delete',
            'customer.connector.service.address.delete'
        ];

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteAddressConsumers);

        parent::afterDelete($addressRepository, $result, $address);
        $consumerInvoker->stopConsumers($deleteAddressConsumers);
    }

    /**
     * @inheritDoc
     *
     * force run the customer address delete consumers after the fixture rollback
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param bool $result
     * @param int $addressId
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDeleteById(
        AddressRepositoryInterface $addressRepository,
        $result,
        $addressId
    ) {
        $deleteAddressConsumers = [
            'customer.monolith.connector.address.delete',
            'customer.connector.service.address.delete'
        ];
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteAddressConsumers);

        parent::afterDeleteById($addressRepository, $result, $addressId);
        $consumerInvoker->stopConsumers($deleteAddressConsumers);
    }
}
