<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerStorefrontConnector\Queue\Consumer\Customer as CustomerConnectorConsumer;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test publish customer save  and delete events to the first queue
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class CustomerStorefrontPublisherTest extends TestCase
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var QueueRepository */
    private $queueRepository;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var CustomerConnectorConsumer  */
    private $customerConnectorConsumer;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->queueRepository = $objectManager->create(QueueRepository::class);
        $this->customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
        $this->encryptor = $objectManager->get(EncryptorInterface::class);
        $this->customerConnectorConsumer = $objectManager->get(CustomerConnectorConsumer::class);
    }

    /**
     * Test customer delete event
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer_with_no_rolling_back.php
     */
    public function testPublishCustomerDeleteMessage()
    {
        $customer = $this->customerRepository->get('customer.norollingback@example.com', 1);
        $this->customerRepository->delete($customer);
        /** @var QueueInterface $monolithDeleteQueue */
        $monolithDeleteQueue = $this->queueRepository->get('amqp', 'customer.monolith.messageBroker.customer.delete');
        /** @var QueueInterface $monolithSaveQueue */
        $monolithSaveQueue = $this->queueRepository->get('amqp', 'customer.monolith.messageBroker.customer.save');
        /** @var EnvelopeInterface $message */
        $monolithSaveMessage = $monolithSaveQueue->dequeue();

        /** @var EnvelopeInterface $monolithDeleteMessage */
        $monolithDeleteMessage = $monolithDeleteQueue->dequeue();
        $messageBody = $monolithDeleteMessage->getBody();

        $unserializedJson = $this->serializer->unserialize($messageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id', $parsedData);
        $this->assertEquals('customer', $parsedData['entity_type']);
        $this->assertEquals('delete', $parsedData['event']);
        $this->assertEquals($customer->getId(), $parsedData['data']['id']);
        $monolithDeleteQueue->acknowledge($monolithDeleteMessage);
        $monolithSaveQueue->acknowledge($monolithSaveMessage);
    }

    /**
     * Test customer save event
     *
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer.php
     */
    public function testPublishCustomerSaveMessage()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        /** @var QueueInterface $queue */
        $queue = $this->queueRepository->get('amqp', 'customer.monolith.messageBroker.customer.save');
        /** @var EnvelopeInterface $message */
        $message = $queue->dequeue();
        $messageBody = $message->getBody();
        $unserializedJson = $this->serializer->unserialize($messageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id', $parsedData);
        $this->assertEquals('customer', $parsedData['entity_type']);
        $this->assertEquals('create', $parsedData['event']);
        $this->assertEquals($customer->getId(), $parsedData['data']['id']);
        $queue->acknowledge($message);
    }
}
