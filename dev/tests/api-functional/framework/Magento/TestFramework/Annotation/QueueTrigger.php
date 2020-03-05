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
class QueueTrigger
{
    private $testWhitelist = ['Magento\GraphQl\Customer\GetCustomerTest','Magento\GraphQl\Customer\GetAddressesTest'];

    /**
     * Handler for 'startTest' event.
     *
     * Sync Magento monolith App data with Customer Storefront Storage.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($test instanceof GraphQlAbstract) {
            if(in_array(get_class($test), $this->testWhitelist)) {
                $this->waitForConsumersToStart();
            }
            else return;
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
            if(in_array(get_class($test), $this->testWhitelist)) {
                $this->waitForConsumersToStop();
            }
            else return;
        }
    }

    /**
     * Wait for all consumers to start
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function waitForConsumersToStart(): void
    {
        $consumers = [
            'customer.monolith.connector.customer.save',
            'customer.connector.service.customer.save',
            'customer.monolith.connector.customer.delete',
            'customer.connector.service.customer.delete',
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
     * Wait for customer-save-consumers to stop.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function waitForConsumersToStop(): void
    {
        $consumers = [
            'customer.monolith.connector.customer.save',
            'customer.connector.service.customer.save',
            'customer.monolith.connector.customer.delete',
            'customer.connector.service.customer.delete',
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
