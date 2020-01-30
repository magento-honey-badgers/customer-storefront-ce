<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\CustomerStorefrontConnector\Model\DataProvider\Address as AddressDataProvider;
use Magento\CustomerStorefrontConnector\Queue\MessageGenerator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handle address save messages
 */
class Address
{
    const ENTITY_TYPE = 'address';

    const SAVE_TOPIC = 'customer.connector.service.saveAddress';

    const DELETE_TOPIC = 'customer.connector.service.deleteAddress';

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
                'correlation_id' => $incomingMessageArray['correlation_id'],
                'entity_type' => self::ENTITY_TYPE,
                'action'=> self::SAVE_ACTION
            ];
            $message = $this->messageGenerator->generate($addressData, $metaData);
        } catch (NoSuchEntityException $e) {
            //TODO
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
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $metadata = [
            'correlation_id' => $incomingMessageArray['correlation_id'],
            'entity_type' => self::ENTITY_TYPE,
            'action' => self::DELETE_ACTION
        ];

        $message = $this->messageGenerator->generate($incomingMessageArray['data'], $metadata);
        $this->publisher->publish(self::DELETE_TOPIC, $message);
    }
}
