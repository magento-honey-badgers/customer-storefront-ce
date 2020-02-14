<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\CustomerStorefrontConnector\Model\CustomerRepositoryWrapper;
use Magento\CustomerStorefrontConnector\Queue\MessageGenerator;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle customer save messages
 */
class Customer
{
    const SAVE_TOPIC = 'customer.connector.service.customer.save';

    const DELETE_TOPIC = 'customer.connector.service.customer.delete';

    /**
     * @var CustomerRepositoryWrapper
     */
    private $customerRepository;

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
     * @param CustomerRepositoryWrapper $customerRepository
     * @param MessageGenerator $messageGenerator
     * @param SerializerInterface $serializer
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryWrapper $customerRepository,
        MessageGenerator $messageGenerator,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
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
        try {
            $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
            $customerId = (int) $incomingMessageArray['data']['id'];
            $customerData = $this->customerRepository->getById($customerId);
            $metaData = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray[MessageGenerator::CORRELATION_ID_KEY],
                MessageGenerator::ENTITY_TYPE_KEY => $incomingMessageArray[MessageGenerator::ENTITY_TYPE_KEY],
                MessageGenerator::EVENT_KEY => $incomingMessageArray[MessageGenerator::EVENT_KEY]
            ];
            $message = $this->messageGenerator->generateSerialized($customerData, $metaData);
            $this->publisher->publish(self::SAVE_TOPIC, $message);
            $this->logger->info('Message processed', [$incomingMessage]);
        } catch (\Exception $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }
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
            $metaData = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray[MessageGenerator::CORRELATION_ID_KEY],
                MessageGenerator::ENTITY_TYPE_KEY => $incomingMessageArray[MessageGenerator::ENTITY_TYPE_KEY],
                MessageGenerator::EVENT_KEY => $incomingMessageArray[MessageGenerator::EVENT_KEY]
            ];

            $message = $this->messageGenerator->generateSerialized($incomingMessageArray['data'], $metaData);
            $this->publisher->publish(self::DELETE_TOPIC, $message);
            $this->logger->info('Message processed', [$incomingMessage]);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }
    }
}
