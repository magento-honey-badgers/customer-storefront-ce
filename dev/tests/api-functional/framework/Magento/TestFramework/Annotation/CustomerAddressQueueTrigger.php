<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\StorefrontTestFixer\ConsumerInvoker;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Trigger queue to process storefront consumers
 */
class CustomerAddressQueueTrigger
{
    /**
     * Handler for 'startTest' event.
     *
     * Sync Magento monolith App data with Catalog Storefront Storage.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($test instanceof GraphQlAbstract) {
            $this->waitForCustomerAndAddressConsumersToStart();
        }
    }
    /**
     * Handler for 'startTest' event.
     *
     * Sync Magento monolith App data with Storefront Storage.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($test instanceof GraphQlAbstract) {
            $this->waitForCustomerAndAddressConsumersToStop();
        }
    }

    private function waitForCustomerAndAddressConsumersToStart(): void
    {
        $consumers = [
            'customer.monolith.connector.address.save',
            'customer.connector.service.address.save',
            'customer.monolith.connector.address.delete',
            'customer.connector.service.address.delete'
        ];

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($consumers);
    }

    /**
     * Wait for asynchronous handlers to log data to file.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function waitForCustomerAndAddressConsumersToStop(): void
    {
        $consumers = [
            'customer.monolith.connector.address.save',
            'customer.connector.service.address.save',
            'customer.monolith.connector.address.delete',
            'customer.connector.service.address.delete'
        ];

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->stopConsumers($consumers);
    }
}
