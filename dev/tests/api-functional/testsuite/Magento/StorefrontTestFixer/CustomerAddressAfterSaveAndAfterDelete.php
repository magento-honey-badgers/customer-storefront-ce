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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;

class CustomerAddressAfterSaveAndAfterDelete extends CustomerAddressStorefrontPublisherPlugin
{
    /**
     * @inheritDoc
     */
    public function afterSave(
        AddressRepositoryInterface $addressRepository,
        AddressInterface $address,
        AddressInterface $addressInput
    ) : AddressInterface {
        $saveAddressConsumers = [
            'customer.monolith.connector.address.save',
            'customer.connector.service.address.save',
        ];
        $address = parent::afterSave($addressRepository, $address, $addressInput);
        return $address;
    }

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
