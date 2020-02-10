<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerStorefrontConnector\Model\CustomerRepositoryWrapper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CustomerStorefrontConnector\Queue\Consumer\Customer as CustomerConnectorConsumer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test combined functioning of synchronizer and connector where customer save and delete events are published to the second queue
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 */
class CustomerConnectorToStorefrontPublisherTest extends TestCase
{
    /** @var CustomerInterface */
    //   private $customer;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface|MockObject */
    private $customerRepositoryMock;

    /**
     * @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var QueueRepository */
    private $queueRepostiory;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var CustomerConnectorConsumer */
    private $customerConnectorConsumer;

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
        $this->customerRepositoryWrapperMock = $this->createPartialMock(CustomerRepositoryWrapper::class,
            ['getById']);
        $this->customerConnectorConsumer = $this->objectManager->create(CustomerConnectorConsumer::class,
            ['customerRepository' => $this->customerRepositoryWrapperMock]);
    }

    /**
     * Test forward customer save event into the connector-consumer into the second queue
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer_with_no_rolling_back.php
     */
    public function testForwardCustomerDeleteMessageToConnectorConsumer()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $this->customerRepository->delete($customer);

        /** @var QueueInterface $monolithQueue */
        $monolithQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.connector.customer.delete');

        /** @var QueueInterface $serviceQueue */
        $serviceQueue = $this->queueRepostiory->get('amqp', 'customer.connector.service.customer.delete');

        /** @var  QueueInterface $monolithSaveQueue */
        $monolithSaveQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.connector.customer.save');

        /** @var EnvelopeInterface $monolithMessage */
        $monolithSaveMessage = $monolithSaveQueue->dequeue();

        /** @var EnvelopeInterface $monolithMessage */
        $monolithMessage = $monolithQueue->dequeue();
        $this->assertNotNull($monolithMessage);
        $unserializedMonolithMessage = $this->serializer->unserialize($monolithMessage->getBody());
        $this->customerConnectorConsumer->forwardCustomerDelete($unserializedMonolithMessage);
        $serviceMessage = $serviceQueue->dequeue();
        $this->assertNotNull($serviceMessage);
        $customerServiceSaveMessageBody = $serviceMessage->getBody();
        $unserializedJson = $this->serializer->unserialize($customerServiceSaveMessageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
      $this->assertArrayHasKey('correlation_id',$parsedData);
      $this->assertEquals('customer',$parsedData['entity_type']);
      $this->assertEquals('delete', $parsedData['event']);
      $this->assertEquals($customer->getId(), $parsedData['data']['id']);

      //Clean up - making sure to acknowledge all the messages from all the queues involved
      $serviceQueue->acknowledge($serviceMessage);
      $monolithQueue->acknowledge($monolithMessage);
      $monolithSaveQueue->acknowledge($monolithSaveMessage);
    }

    /**
     * Once customer is saved in monolith, the message gets published to first queue from where it gets picked up by the Consumer on Connector side and
     * gets published to the second queue
     *
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer.php
     * @magentoAppArea adminhtml
     *
     */
    public function testForwardCustomerSaveMessageToConnectorConsumer() : void
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $customerId = $customer->getId();

        /** @var QueueInterface $monolithQueue */
        $monolithQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.connector.customer.save');
        /** @var QueueInterface $serviceQueue */
        $serviceQueue = $this->queueRepostiory->get('amqp', 'customer.connector.service.customer.save');
        /** @var EnvelopeInterface $monolithMessage */
        $monolithMessage = $monolithQueue->dequeue();
        $unserializedMonolithMessage = $this->serializer->unserialize($monolithMessage->getBody());
        $customerData = include __DIR__ . '/../_files/customer_data.php';
      //  $customerData = include '/opt/local/www/apache2/html/CustomerStorefront/CustomerStorefrontApp/dev/tests/integration/testsuite/Magento/CustomerStorefrontConnector/_files/customer_data.php';
        $this->customerRepositoryWrapperMock->expects($this->once())->method('getById')->with($customerId)->willReturn($customerData);
        $this->customerConnectorConsumer->forwardCustomerChanges($unserializedMonolithMessage);

        $customerSaveMessage = $serviceQueue->dequeue();
        $messageBody = $customerSaveMessage->getBody();
        $unserializedJson = $this->serializer->unserialize($messageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id',$parsedData);
        $this->assertEquals('customer',$parsedData['entity_type']);
        $this->assertEquals('create', $parsedData['event']);
        $this->assertNotEmpty($parsedData['data']); $this->assertEquals('customer@example.com', $parsedData['data'][0]['email']);
        $this->assertEquals('Johny', $parsedData['data'][0]['firstname']);
        $this->assertEquals('Smith', $parsedData['data'][0]['lastname']);
        $this->assertEquals('01-01-1970', $parsedData['data'][0]['dob']);
        $serviceQueue->acknowledge($customerSaveMessage);
        $monolithQueue->acknowledge($monolithMessage);
    }
}
