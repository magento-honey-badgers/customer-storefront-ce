<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test customer save consumer
 */
class CustomerTest extends TestCase
{
    /**
     * @var Customer
     */
    private $customerConsumer;

    /**
     * @var PublisherInterface|MockObject
     */
    private $publisherMock;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    protected function setup()
    {
        $this->markTestSkipped('Test fails because REST calls fail in integration environment');
        $objectManager = Bootstrap::getObjectManager();

        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->publisherMock = $this->createMock(PublisherInterface::class);
        $this->customerConsumer = $objectManager->create(
            Customer::class,
            ['publisher' => $this->publisherMock]
        );
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     */
    public function testforwardCustomerChanges()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $mockMessage = $this->serializer->serialize([
            'correlation_id' => '99',
            'data' => ['id' => $customer->getId()]
        ]);

        $this->publisherMock
            ->expects($this->once())
            ->method('publish')
            ->with(
                Customer::SAVE_TOPIC,
                $this->logicalAnd(
                    $this->stringContains('"correlation_id":"99","entity_type":"customer","event":"save"'),
                    $this->stringContains(
                        '"data":{"id":"' . $customer->getId() . '","dob":"1970-01-01","email":"customer@example.com"'
                    ),
                    $this->stringContains('"prefix":"Mr.","firstname":"John","lastname":"Smith"')
                )
            );

        $this->customerConsumer->forwardCustomerChanges($mockMessage);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     */
    public function testforwardCustomerDelete()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $mockMessage = $this->serializer->serialize([
            'correlation_id' => '99',
            'data' => ['id' => $customer->getId()]
        ]);

        $this->publisherMock
            ->expects($this->once())
            ->method('publish')
            ->with(
                Customer::DELETE_TOPIC,
                $this->logicalAnd(
                    $this->stringContains('"correlation_id":"99","entity_type":"customer","event":"delete"'),
                    $this->stringContains('"data":{"id":"' . $customer->getId() . '"}}')
                )
            );

        $this->customerConsumer->forwardCustomerDelete($mockMessage);
    }
}
