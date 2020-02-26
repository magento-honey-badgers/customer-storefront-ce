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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;

class CustomerAfterSaveAndAfterDelete extends CustomerStorefrontPublisherPlugin
{
    /**
     * @inheritDoc
     */
    public function afterSave(
        CustomerRepository $customerRepository,
        CustomerInterface $customer,
        CustomerInterface $customerInput
    ) : CustomerInterface {
        $saveConsumers = [
            'customer.monolith.connector.customer.save',
            'customer.connector.service.customer.save',
        ];
//        try {
//            $this->startConsumers($saveConsumers);
//        } catch (LocalizedException $e) {
//            $e->getMessage();
//        }
        sleep(10);
        $this->startConsumers($saveConsumers);
        $customer = parent::afterSave($customerRepository, $customer, $customerInput);
        return $customer;
    }

    public function afterDelete(
        CustomerRepository $customerRepository,
        $result,
        CustomerInterface $customer
    ) {
        $deleteConsumers = [
            'customer.monolith.connector.customer.delete',
            'customer.connector.service.customer.delete'

        ];
        try {
            $this->startConsumers($deleteConsumers);
        } catch (LocalizedException $e) {
        }
        return parent::afterDelete($customerRepository, $result, $customer);
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
        $this->startConsumers($deleteConsumers);
        return parent::afterDeleteById($customerRepository, $result, $customerId);
    }

    /**
     *  start the consumers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function startConsumersFromFactory(array $consumers)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerFactory $consumerFactory */
        $consumerFactory = $objectManager->get(ConsumerFactory::class);
        foreach ($consumers as $consumer) {
            $consumer = $consumerFactory->get($consumer);
            $consumer->process();
        }
        /** @var //ConsumerInvoker $consumerInvoker */
  //      $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
  //      $consumerInvoker->invoke(false);
//        $consumerInvoker->invoke(false);
    }

    private function startConsumers(array $consumers)
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
}
