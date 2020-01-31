<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\CustomerStorefrontConnector\Model\DataProvider\Address as AddressDataProvider;
use Magento\CustomerStorefrontConnector\Queue\MessageGenerator;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle address save messages
 */
class Address
{
    const ENTITY_TYPE = 'address';

    const SAVE_TOPIC = 'customer.connector.service.address.save';

    const DELETE_TOPIC = 'customer.connector.service.address.delete';

    const SAVE_ACTION = 'save';

    const DELETE_ACTION = 'delete';

    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

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
     * @param AddressDataProvider $addressDataProvider
     * @param MessageGenerator $messageGenerator
     * @param SerializerInterface $serializer
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddressDataProvider $addressDataProvider,
        MessageGenerator $messageGenerator,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->addressDataProvider = $addressDataProvider;
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
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $addressId = (int) $incomingMessageArray['data']['id'];
        try {
            $addressData = $this->addressDataProvider->getData($addressId);
            $metaData = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray['correlation_id'],
                MessageGenerator::ENTITY_TYPE_KEY => self::ENTITY_TYPE,
                MessageGenerator::EVENT_KEY => self::SAVE_ACTION
            ];
            $message = $this->messageGenerator->generateSerialized($addressData, $metaData);
        } catch (\Exception $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }

        $this->publisher->publish(self::SAVE_TOPIC, $message);
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
            $metadata = [
                MessageGenerator::CORRELATION_ID_KEY => $incomingMessageArray['correlation_id'],
                MessageGenerator::ENTITY_TYPE_KEY => self::ENTITY_TYPE,
                MessageGenerator::EVENT_KEY => self::DELETE_ACTION
            ];

            $message = $this->messageGenerator->generateSerialized($incomingMessageArray['data'], $metadata);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Message could not be processed: ' . $e->getMessage(), [$incomingMessage]);
            throw $e;
        }

        $this->publisher->publish(self::DELETE_TOPIC, $message);
    }
}
