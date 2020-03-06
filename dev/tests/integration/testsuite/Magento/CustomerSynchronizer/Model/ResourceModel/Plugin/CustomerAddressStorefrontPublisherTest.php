<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\QueueMessageHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test publish customer address save event
 */
class CustomerAddressStorefrontPublisherTest extends TestCase
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var QueueMessageHelper */
    private $messageHelper;

    private static $queues = [
        'monolithCustomerSave' => 'customer.monolith.messageBroker.customer.save',
        'monolithAddressSave' => 'customer.monolith.messageBroker.address.save',
        'monolithCustomerDelete' => 'customer.monolith.messageBroker.customer.delete',
        'monolithAddressDelete' => 'customer.monolith.messageBroker.address.delete'
    ];

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
        $this->addressRepository = $objectManager->get(AddressRepositoryInterface::class);
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
     * @magentoDataFixture Magento/CustomerSynchronizer/_files/customer_with_address.php
     */
    public function testPublishCustomerAddressSaveMessage()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $customerAddress = $this->addressRepository->getById($customer->getDefaultBilling());
        $customerAddress->setCompany('New updated CompanyName');
        $this->addressRepository->save($customerAddress);

        $addressSaveMessage = $this->messageHelper->popMessage(self::$queues['monolithAddressSave']);
        $addressSaveMessageData = $this->serializer->unserialize($addressSaveMessage);
        $this->assertNotEmpty($addressSaveMessageData);
        $this->assertArrayHasKey('correlation_id', $addressSaveMessageData);
        $this->assertEquals('address', $addressSaveMessageData['entity_type']);
        $this->assertEquals('update', $addressSaveMessageData['event']);
        $this->assertEquals($customerAddress->getId(), $addressSaveMessageData['data']['id']);
    }
}
