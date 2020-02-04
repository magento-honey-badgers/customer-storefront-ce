<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\CustomerStorefrontConnector\Model\DataProvider\Customer as CustomerDataProvider;
use Magento\CustomerStorefrontConnector\Queue\MessageGenerator;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle customer save messages
 */
class Customer
{
    const ENTITY_TYPE = 'customer';

    const SAVE_TOPIC = 'customer.connector.service.customer.save';

    const DELETE_TOPIC = 'customer.connector.service.customer.delete';

    const SAVE_ACTION = 'save';

    const DELETE_ACTION = 'delete';

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var MessageGenerator
     */
    private $messageGenerator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CustomerDataProvider $customerDataProvider
     * @param MessageGenerator $messageGenerator
     * @param SerializerInterface $serializer
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerDataProvider $customerDataProvider,
        MessageGenerator $messageGenerator,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->customerDataProvider = $customerDataProvider;
        $this->messageGenerator = $messageGenerator;
        $this->serializer = $serializer;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * Send customer data to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function forwardCustomerChanges(string $incomingMessage): void
    {
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $customerId = (int) $incomingMessageArray['data']['id'];
        try {
            $customerData = $this->customerDataProvider->getData($customerId);
            $metadata = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray['correlation_id'],
                MessageGenerator::ENTITY_TYPE_KEY => self::ENTITY_TYPE,
                MessageGenerator::EVENT_KEY => self::SAVE_ACTION
            ];
            $message = $this->messageGenerator->generateSerialized($customerData, $metadata);
        } catch (\Exception $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }

        $this->logger->info('Message processed', [$incomingMessage]);

        $this->publisher->publish(self::SAVE_TOPIC, $message);
    }

    /**
     * Forward deleted customer id to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function forwardCustomerDelete(string $incomingMessage): void
    {
        try {
            $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
            $metadata = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray['correlation_id'],
                MessageGenerator::ENTITY_TYPE_KEY => self::ENTITY_TYPE,
                MessageGenerator::EVENT_KEY => self::DELETE_ACTION
            ];

            $message = $this->messageGenerator->generateSerialized($incomingMessageArray['data'], $metadata);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }

        $this->publisher->publish(self::DELETE_TOPIC, $message);
    }

}
