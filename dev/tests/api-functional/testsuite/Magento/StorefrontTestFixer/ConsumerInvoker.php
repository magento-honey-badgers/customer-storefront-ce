<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;

/**
 * Invoke consumers to push data from Magento Monolith to Customer Storefront
 */
class ConsumerInvoker
{
    /**
     * @param array $consumers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startConsumers(array $consumers)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var PublisherConsumerController $publisherConsumerController */
        $publisherConsumerController = $objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => $consumers,
                'logFilePath' => TESTS_TEMP_DIR . "/CustomerStorefrontMessageQueueTestLog.txt",
                'maxMessages' => 500,
                'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
            ]
        );
        try {
            $publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $e->getMessage();
        } catch (PreconditionFailedException $e) {
            $e->getMessage();
        }
    }

    public function stopConsumers(array $consumers)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var PublisherConsumerController $publisherConsumerController */
        $publisherConsumerController = $objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => $consumers,
                'logFilePath' => TESTS_TEMP_DIR . "/CustomerStorefrontMessageQueueTestLog.txt",
                'maxMessages' => 500,
                'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
            ]
        );
        $publisherConsumerController->stopConsumers();
    }
}
