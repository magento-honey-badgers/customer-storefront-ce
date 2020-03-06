<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerMessageBroker\Queue\Consumer;

use Magento\CustomerMessageBroker\Model\AddressRepositoryWrapper;
use Magento\CustomerMessageBroker\Queue\MessageGenerator;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle address save messages
 */
class Address
{
    const SAVE_TOPIC = 'customer.messageBroker.service.address.save';

    const DELETE_TOPIC = 'customer.messageBroker.service.address.delete';

    /**
     * @var AddressRepositoryWrapper
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageGenerator
     */
    private $messageGenerator;

    /**
     * @param AddressRepositoryWrapper $addressRepository
     * @param MessageGenerator $messageGenerator
     * @param SerializerInterface $serializer
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddressRepositoryWrapper $addressRepository,
        MessageGenerator $messageGenerator,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->addressRepository = $addressRepository;
        $this->messageGenerator = $messageGenerator;
        $this->serializer = $serializer;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * Send address data to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function forwardAddressChanges(string $incomingMessage): void
    {
        try {
            $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
            $addressId = (int) $incomingMessageArray['data']['id'];
            $addressData = $this->addressRepository->getById($addressId);
            $metaData = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray[MessageGenerator::CORRELATION_ID_KEY],
                MessageGenerator::ENTITY_TYPE_KEY => $incomingMessageArray[MessageGenerator::ENTITY_TYPE_KEY],
                MessageGenerator::EVENT_KEY => $incomingMessageArray[MessageGenerator::EVENT_KEY]
            ];
            $message = $this->messageGenerator->generateSerialized($addressData, $metaData);
            $this->publisher->publish(self::SAVE_TOPIC, $message);
            $this->logger->info('Message processed', [$incomingMessage]);
        } catch (\Exception $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }
    }

    /**
     * Forward deleted address ID to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function forwardAddressDelete(string $incomingMessage): void
    {
        try {
            $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
            $metaData =  [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray[MessageGenerator::CORRELATION_ID_KEY],
                MessageGenerator::ENTITY_TYPE_KEY => $incomingMessageArray[MessageGenerator::ENTITY_TYPE_KEY],
                MessageGenerator::EVENT_KEY => $incomingMessageArray[MessageGenerator::EVENT_KEY]
            ];

            $message = $this->messageGenerator->generateSerialized($incomingMessageArray['data'], $metaData);
            $this->publisher->publish(self::DELETE_TOPIC, $message);
            $this->logger->info('Message processed', [$incomingMessage]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }
    }
}
