<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\CustomerStorefrontPublisherPlugin;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Plugin class to to delete customers and invoke consumers
 *
 * After customers are deleted, consumers are started to clean up the queues on both monolith and storefront side
 */
class CustomerAfterSaveAndAfterDelete extends CustomerStorefrontPublisherPlugin
{
    public function afterDelete(
        CustomerRepository $customerRepository,
        $result,
        CustomerInterface $customer
    ) {
        $deleteConsumers = [
            'customer.monolith.connector.customer.delete',
            'customer.connector.service.customer.delete'

        ];

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteConsumers);

        parent::afterDelete($customerRepository, $result, $customer);
        $consumerInvoker->stopConsumers($deleteConsumers);
    }

    public function afterDeleteById(
        CustomerRepository $customerRepository,
        $result,
        $customerId
    ) {
        $deleteConsumers = [
            'customer.monolith.connector.customer.delete',
            'customer.connector.service.customer.delete'

        ];

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteConsumers);

        parent::afterDeleteById($customerRepository, $result, $customerId);
        $consumerInvoker->stopConsumers($deleteConsumers);
    }
}
