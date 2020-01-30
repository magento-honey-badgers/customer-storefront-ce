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

    const SAVE_TOPIC = 'customer.connector.service.saveCustomer';

    const DELETE_TOPIC = 'customer.connector.service.deleteCustomer';

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
                'correlation_id' => $incomingMessageArray['correlation_id'],
                'entity_type' => self::ENTITY_TYPE,
                'action' => self::SAVE_ACTION
            ];
            $message = $this->messageGenerator->generate($customerData, $metadata);
        } catch (\Exception $e) {
            //TODO how to handle exception??
        }

        $this->publisher->publish(self::SAVE_TOPIC, $message);
    }

    /**
     * Forward deleted customer id to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function forwardCustomerDelete(string $incomingMessage): void
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
