<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerMessageBroker\Queue;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerMessageBroker\Model\CustomerRepositoryWrapper;
use Magento\CustomerMessageBroker\Queue\Consumer\Customer as CustomerMessageBrokerConsumer;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test combined functioning of synchronizer and messageBroker
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 */
class CustomerMessageBrokerToStorefrontPublisherTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var QueueRepository */
    private $queueRepostiory;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var CustomerMessageBrokerConsumer */
    private $customerMessageBrokerConsumer;

    /** @var CustomerRepositoryWrapper|MockObject  */
    private $customerRepositoryWrapperMock;

    protected function setup()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->queueRepostiory = $this->objectManager->create(QueueRepository::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->encryptor = $this->objectManager->get(EncryptorInterface::class);
        $this->customerRepositoryWrapperMock = $this->createPartialMock(
            CustomerRepositoryWrapper::class,
            ['getById']
        );
        $this->customerMessageBrokerConsumer = $this->objectManager->create(
            CustomerMessageBrokerConsumer::class,
            ['customerRepository' => $this->customerRepositoryWrapperMock]
        );
    }

    /**
     * Forward customer delete event
     *
     * Test published customer delete event from synchronizer to messageBroker
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CustomerSynchronizer/_files/customer_with_no_rolling_back.php
     */
    public function testForwardCustomerDeleteMessageToMessageBrokerConsumer()
    {
        $customer = $this->customerRepository->get('customer.norollingback@example.com', 1);
        $this->customerRepository->delete($customer);

        /** @var QueueInterface $monolithQueue */
        $monolithQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.messageBroker.customer.delete');

        /** @var QueueInterface $serviceQueue */
        $serviceQueue = $this->queueRepostiory->get('amqp', 'customer.messageBroker.service.customer.delete');

        /** @var  QueueInterface $monolithSaveQueue */
        $monolithSaveQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.messageBroker.customer.save');

        /** @var EnvelopeInterface $monolithMessage */
        $monolithSaveMessage = $monolithSaveQueue->dequeue();

        /** @var EnvelopeInterface $monolithMessage */
        $monolithMessage = $monolithQueue->dequeue();
        $this->assertNotNull($monolithMessage);
        $unserializedMonolithMessage = $this->serializer->unserialize($monolithMessage->getBody());
        $this->customerMessageBrokerConsumer->forwardCustomerDelete($unserializedMonolithMessage);
        $serviceMessage = $serviceQueue->dequeue();
        $this->assertNotNull($serviceMessage);
        $customerServiceSaveMessageBody = $serviceMessage->getBody();
        $unserializedJson = $this->serializer->unserialize($customerServiceSaveMessageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id', $parsedData);
        $this->assertEquals('customer', $parsedData['entity_type']);
        $this->assertEquals('delete', $parsedData['event']);
        $this->assertEquals($customer->getId(), $parsedData['data']['id']);

        //Clean up - making sure to acknowledge all the messages from all the queues involved
        $serviceQueue->acknowledge($serviceMessage);
        $monolithQueue->acknowledge($monolithMessage);
        $monolithSaveQueue->acknowledge($monolithSaveMessage);
    }

    /**
     * Forward customer save event to MessageBroker consumer
     *
     * Test published customer save event from synchronizer to messageBroker
     *
     * @magentoDataFixture Magento/CustomerSynchronizer/_files/customer.php
     * @magentoAppArea adminhtml
     *
     */
    public function testForwardCustomerSaveMessageToMessageBrokerConsumer() : void
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $customerId = $customer->getId();

        /** @var QueueInterface $monolithQueue */
        $monolithQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.messageBroker.customer.save');
        /** @var QueueInterface $serviceQueue */
        $serviceQueue = $this->queueRepostiory->get('amqp', 'customer.messageBroker.service.customer.save');
        /** @var EnvelopeInterface $monolithMessage */
        $monolithMessage = $monolithQueue->dequeue();
        $unserializedMonolithMessage = $this->serializer->unserialize($monolithMessage->getBody());
        $customerData = include __DIR__ . '/../_files/customer_data.php';
        $this->customerRepositoryWrapperMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerData);
        $this->customerMessageBrokerConsumer->forwardCustomerChanges($unserializedMonolithMessage);

        $customerSaveMessage = $serviceQueue->dequeue();
        $messageBody = $customerSaveMessage->getBody();
        $unserializedJson = $this->serializer->unserialize($messageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id', $parsedData);
        $this->assertEquals('customer', $parsedData['entity_type']);
        $this->assertEquals('create', $parsedData['event']);
        $this->assertNotEmpty($parsedData['data']);
        $this->assertEquals('customer@example.com', $parsedData['data'][0]['email']);
        $this->assertEquals('Johny', $parsedData['data'][0]['firstname']);
        $this->assertEquals('Smith', $parsedData['data'][0]['lastname']);
        $this->assertEquals('01-01-1970', $parsedData['data'][0]['dob']);
        $serviceQueue->acknowledge($customerSaveMessage);
        $monolithQueue->acknowledge($monolithMessage);
    }
}
