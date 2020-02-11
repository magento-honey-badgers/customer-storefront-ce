<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test publish customer address save event
 */
class CustomerAddressStorefrontPublisherTest extends TestCase
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var QueueRepository */
    private $queueRepostiory;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->queueRepostiory = $objectManager->create(QueueRepository::class);
        $this->customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
        $this->encryptor = $objectManager->get(EncryptorInterface::class);
        $this->addressRepository = $objectManager->get(AddressRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontSynchronizer/_files/customer_with_address.php
     */
    public function testReadMessageCustomerAddressSave()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        /** @var AddressInterface $customerAddress */
        $customerAddress = $this->addressRepository->getById($customer->getDefaultBilling());
        $customerAddress->setCompany('New updated CompanyName');
        $this->addressRepository->save($customerAddress);
        /** @var QueueInterface $queue */
        $queue = $this->queueRepostiory->get('amqp', 'customer.monolith.connector.address.save' );
        /** @var EnvelopeInterface $message */
        $message = $queue->dequeue();
        $messageBody = $message->getBody();
        $unserializedJson = $this->serializer->unserialize($messageBody);
        //de-serialize it the second time to get array format.
        $parsedData = $this->serializer->unserialize($unserializedJson);
        $this->assertNotEmpty($parsedData);
        $this->assertArrayHasKey('correlation_id',$parsedData);
        $this->assertEquals('address',$parsedData['entity_type']);
        $this->assertEquals('save', $parsedData['event']);
        $this->assertEquals($customerAddress->getId(), $parsedData['data']['id']);
        $queue->acknowledge($message);
    }
}