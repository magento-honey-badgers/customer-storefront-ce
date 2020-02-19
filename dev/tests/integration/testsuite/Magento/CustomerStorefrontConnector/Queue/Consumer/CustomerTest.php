<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontConnector\Model\CustomerRepositoryWrapper;
use Magento\CustomerStorefrontConnector\Queue\Consumer\Customer as CustomerConsumer;
use Magento\CustomerStorefrontConnector\QueueManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test combined functioning of synchronizer and connector
 */
class CustomerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var CustomerConsumer */
    private $customerConnectorConsumer;

    /** @var CustomerRepositoryWrapper|MockObject */
    private $customerRepositoryWrapperMock;

    /** @var QueueManager */
    private $queueManager;

    private static $queues = [
        'monolithCustomerSave' => 'customer.monolith.connector.customer.save',
        'monolithAddressSave' => 'customer.monolith.connector.address.save',
        'monolithCustomerDelete' => 'customer.monolith.connector.customer.delete',
        'monolithAddressDelete' => 'customer.monolith.connector.address.delete',
        'serviceCustomerSave' => 'customer.connector.service.customer.save',
        'serviceAddressSave' => 'customer.connector.service.address.save',
        'serviceCustomerDelete' => 'customer.connector.service.customer.delete',
        'serviceAddressDelete' => 'customer.connector.service.address.delete'
    ];


    protected function setup()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->queueManager = $this->objectManager->get(QueueManager::class);

        //CustomerRepositoryWrapper must be mocked because REST calls are not possible in integration test env
        $this->customerRepositoryWrapperMock = $this->createPartialMock(
            CustomerRepositoryWrapper::class,
            ['getById']
        );
        $this->customerConnectorConsumer = $this->objectManager->create(
            CustomerConsumer::class,
            ['customerRepository' => $this->customerRepositoryWrapperMock]
        );
    }

    /**
     * Clean up queues between tests
     */
    protected function tearDown()
    {
        $this->queueManager->cleanQueues(self::$queues);
    }

    /**
     * Clean up queues of rollback messages
     */
    public static function tearDownAfterClass()
    {
        $queueManager = Bootstrap::getObjectManager()->get(QueueManager::class);
        $queueManager->cleanQueues(self::$queues);
    }

    /**
     * Forward customer delete event
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer_with_no_rolling_back.php
     */
    public function testForwardCustomerDeleteMessage()
    {
        $customer = $this->customerRepository->get('customer.norollingback@example.com', 1);
        $this->customerRepository->delete($customer);

        $monolithDeleteMessage = $this->queueManager->popMessage(self::$queues['monolithCustomerDelete']);
        $this->customerConnectorConsumer->forwardCustomerDelete($monolithDeleteMessage);
        $serviceDeleteMessage = $this->queueManager->popMessage(self::$queues['serviceCustomerDelete']);

        $monolithDeleteData = $this->serializer->unserialize($monolithDeleteMessage);
        $serviceDeleteData = $this->serializer->unserialize($serviceDeleteMessage);

        $this->assertNotEmpty($serviceDeleteData);
        $this->assertArrayHasKey('correlation_id', $serviceDeleteData);
        $this->assertEquals($monolithDeleteData['correlation_id'], $serviceDeleteData['correlation_id']);
        $this->assertEquals('customer', $serviceDeleteData['entity_type']);
        $this->assertEquals('delete', $serviceDeleteData['event']);
        $this->assertEquals($customer->getId(), $serviceDeleteData['data']['id']);
        $this->assertEquals($monolithDeleteData['data']['id'], $serviceDeleteData['data']['id']);
    }

    /**
     * Forward customer save event to Connector consumer
     *
     * @param array $customerData
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer.php
     * @dataProvider customerDataProvider
     */
    public function testForwardCustomerSaveMessage(array $customerData)
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);

        $monolithSaveMessage = $this->queueManager->popMessage(self::$queues['monolithCustomerSave']);
        $this->customerRepositoryWrapperMock->expects($this->once())
            ->method('getById')
            ->with($customer->getId())
            ->willReturn($customerData);
        $this->customerConnectorConsumer->forwardCustomerChanges($monolithSaveMessage);
        $serviceSaveMessage = $this->queueManager->popMessage(self::$queues['serviceCustomerSave']);

        $monolithSaveMessageData = $this->serializer->unserialize($monolithSaveMessage);
        $serviceSaveMessageData = $this->serializer->unserialize($serviceSaveMessage);

        $this->assertNotEmpty($serviceSaveMessageData);
        $this->assertArrayHasKey('correlation_id', $serviceSaveMessageData);
        $this->assertEquals($monolithSaveMessageData['correlation_id'], $serviceSaveMessageData['correlation_id']);
        $this->assertEquals('customer', $serviceSaveMessageData['entity_type']);
        $this->assertEquals($monolithSaveMessageData['entity_type'], $serviceSaveMessageData['entity_type']);
        $this->assertEquals('create', $serviceSaveMessageData['event']);
        $this->assertEquals($monolithSaveMessageData['event'], $serviceSaveMessageData['event']);
        $this->assertNotEmpty($serviceSaveMessageData['data']);
        $this->assertEquals('customer@example.com', $serviceSaveMessageData['data']['email']);
        $this->assertEquals('Johny', $serviceSaveMessageData['data']['firstname']);
        $this->assertEquals('Smith', $serviceSaveMessageData['data']['lastname']);
        $this->assertEquals('01-01-1970', $serviceSaveMessageData['data']['dob']);
    }

    /**
     * Customer data to mock REST response
     *
     * @return array
     */
    public function customerDataProvider()
    {
        return [
            [
                [
                    'store_id' => 1,
                    'website_id' => 1,
                    'default_billing' => '0',
                    'default_shipping' => '0',
                    'dob' => '01-01-1970',
                    'email' => 'customer@example.com',
                    'prefix' => 'Mr.',
                    'firstname' => 'Johny',
                    'middlename' => 'A',
                    'lastname' => 'Smith',
                    'suffix' => 'Esq',
                    'gender' => 0,
                    'taxvat' => '12',
                    'addresses' => [],
                    'extension_attributes' => [
                        'is_subscribed' => false
                    ],
                    'custom_attributes' => []
                ]
            ]
        ];
    }
}
