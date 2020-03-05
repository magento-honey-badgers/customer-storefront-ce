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
    /**
     * @inheritDoc
     *
     * Run the consumers after the customers are deleted from fixture rollbacks
     *
     * @param CustomerRepository $customerRepository
     * @param bool $result
     * @param CustomerInterface $customer
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDelete(
        CustomerRepository $customerRepository,
        $result,
        CustomerInterface $customer
    ) {
        $deleteConsumers = [
            'customer.monolith.messageBroker.customer.delete',
            'customer.messageBroker.service.customer.delete'

        ];

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteConsumers);

        parent::afterDelete($customerRepository, $result, $customer);
        $consumerInvoker->stopConsumers($deleteConsumers);
    }

    /**
     * @inheritDoc
     *
     * Run the consumers after customers are deleted from fixture rollbacks
     *
     * @param CustomerRepository $customerRepository
     * @param bool $result
     * @param int $customerId
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDeleteById(
        CustomerRepository $customerRepository,
        $result,
        $customerId
    ) {
        $deleteConsumers = [
            'customer.monolith.messageBroker.customer.delete',
            'customer.messageBroker.service.customer.delete'

        ];

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteConsumers);

        parent::afterDeleteById($customerRepository, $result, $customerId);
        $consumerInvoker->stopConsumers($deleteConsumers);
    }
}
