<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\QueueMessageHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test publish customer save  and delete events to the first queue
 */
class CustomerStorefrontPublisherTest extends TestCase
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var QueueMessageHelper */
    private $messageHelper;

    private static $queues = [
        'monolithCustomerSave' => 'customer.monolith.connector.customer.save',
        'monolithAddressSave' => 'customer.monolith.connector.address.save',
        'monolithCustomerDelete' => 'customer.monolith.connector.customer.delete',
        'monolithAddressDelete' => 'customer.monolith.connector.address.delete'
    ];

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
        $this->messageHelper = $objectManager->get(QueueMessageHelper::class);
    }

    /**
     * Clean up queues between tests
     */
    protected function tearDown()
    {
        $this->messageHelper->acknowledgeAllMessages(self::$queues);
    }

    /**
     * Clean up queues of rollback messages
     */
    public static function tearDownAfterClass()
    {
        $messageHelper = Bootstrap::getObjectManager()->get(QueueMessageHelper::class);
        $messageHelper->acknowledgeAllMessages(self::$queues);
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

        $customerDeleteMessage = $this->messageHelper->popMessage(self::$queues['monolithCustomerDelete']);
        $customerDeleteMessageData = $this->serializer->unserialize($customerDeleteMessage);
        $this->assertNotEmpty($customerDeleteMessageData);
        $this->assertArrayHasKey('correlation_id', $customerDeleteMessageData);
        $this->assertEquals('customer', $customerDeleteMessageData['entity_type']);
        $this->assertEquals('delete', $customerDeleteMessageData['event']);
        $this->assertEquals($customer->getId(), $customerDeleteMessageData['data']['id']);
    }

    /**
     * Test customer save event
     *
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer.php
     */
    public function testPublishCustomerSaveMessage()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);

        $customerSaveMessage = $this->messageHelper->popMessage(self::$queues['monolithCustomerSave']);
        $customerSaveMessageData = $this->serializer->unserialize($customerSaveMessage);
        $this->assertNotEmpty($customerSaveMessageData);
        $this->assertArrayHasKey('correlation_id', $customerSaveMessageData);
        $this->assertEquals('customer', $customerSaveMessageData['entity_type']);
        $this->assertEquals('create', $customerSaveMessageData['event']);
        $this->assertEquals($customer->getId(), $customerSaveMessageData['data']['id']);
    }
}
