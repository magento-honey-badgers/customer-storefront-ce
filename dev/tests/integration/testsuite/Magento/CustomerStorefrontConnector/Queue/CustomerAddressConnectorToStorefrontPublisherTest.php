<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\CustomerStorefrontConnector\Model\AddressRepositoryWrapper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CustomerStorefrontConnector\Queue\Consumer\Address as CustomerAddressConnectorConsumer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test combined functioning of synchronizer and connector
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 */
class CustomerAddressConnectorToStorefrontPublisherTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var QueueRepository */
    private $queueRepostiory;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var CustomerAddressConnectorConsumer */
    private $customerAddressConnectorConsumer;

    /** @var AddressRepositoryWrapper|MockObject  */
    private $customerAddressRepositoryWrapperMock;

    protected function setup()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->queueRepostiory = $this->objectManager->create(QueueRepository::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->encryptor = $this->objectManager->get(EncryptorInterface::class);
        $this->customerAddressRepositoryWrapperMock = $this->createPartialMock(AddressRepositoryWrapper::class,
            ['getById']);
        $this->customerAddressConnectorConsumer = $this->objectManager->create(CustomerAddressConnectorConsumer::class,
            ['addressRepository' => $this->customerAddressRepositoryWrapperMock]);
    }

    /**
     * Test forward customer address change events Connector
     *
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer_with_address.php
     * @magentoAppArea adminhtml
     */
    public function testForwardCustomerAddressChangesToConnectorConsumer() : void
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $customerId = $customer->getId();
        /** @var AddressInterface $customerAddress */
        $customerAddress = $this->addressRepository->getById($customer->getDefaultBilling());
        $addressId = $customerAddress->getId();
        /** @var QueueInterface $monolithAddressQueue */
        $monolithAddressQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.connector.address.save');
        /** @var QueueInterface $monolithCustomerSaveQueue */
        $monolithCustomerSaveQueue = $this->queueRepostiory->get('amqp', 'customer.monolith.connector.customer.save');
        /** @var QueueInterface $serviceAddressQueue */
        $serviceAddressQueue = $this->queueRepostiory->get('amqp', 'customer.connector.service.address.save');

        /** @var EnvelopeInterface $monolithAddressSaveMessage */
        $monolithAddressSaveMessage = $monolithAddressQueue->dequeue();
        $monolithCustomerSaveMessage = $monolithCustomerSaveQueue ->dequeue();

        $unserializedMonolithMessage = $this->serializer->unserialize($monolithAddressSaveMessage->getBody());
       // $customerAddressData = include __DIR__ . '/../_files/customer_address_data.php';
       $customerAddressData = include '/opt/local/www/apache2/html/CustomerStorefront/CustomerStorefrontApp/dev/tests/integration/testsuite/Magento/CustomerStorefrontConnector/_files/customer_address_data.php';
        $this->customerAddressRepositoryWrapperMock->expects($this->once())
            ->method('getById')->with($addressId)->willReturn($customerAddressData);
        $this->customerAddressConnectorConsumer->forwardAddressChanges($unserializedMonolithMessage);

        $customerSaveMessage = $serviceAddressQueue->dequeue();
        $messageBody = $customerSaveMessage->getBody();
        $unserializedJson = $this->serializer->unserialize($messageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id',$parsedData);
        $this->assertEquals('address',$parsedData['entity_type']);
        $this->assertEquals('save', $parsedData['event']);
        $this->assertNotEmpty($parsedData['data']);
        $this->assertEquals($addressId, $parsedData['data'][0]['id']);
        $this->assertEquals('Green str, 67', $parsedData['data'][0]['street'][0]);
        $this->assertEquals($addressId, $parsedData['data'][0]['id']);
        $this->assertTrue($parsedData['data'][0]['default_shipping']);
        $this->assertEquals('CityM', $parsedData['data'][0]['city']);

        // Clean up all the queues by acknowledging the messages the test generated
        $monolithAddressQueue->acknowledge($monolithAddressSaveMessage);
        $monolithCustomerSaveQueue->acknowledge($monolithCustomerSaveMessage);
        $serviceAddressQueue->acknowledge($customerSaveMessage);
    }
}