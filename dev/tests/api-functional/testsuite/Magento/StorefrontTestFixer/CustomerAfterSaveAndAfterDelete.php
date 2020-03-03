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
       // $this->startConsumers($deleteConsumers);
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->startConsumers($deleteConsumers);

        parent::afterDeleteById($customerRepository, $result, $customerId);
        $consumerInvoker->stopConsumers($deleteConsumers);
    }

}
