<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\Helper;

use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Formats Event Data
 */
class EventPublisher
{
    /** @var PublisherInterface  */
    private $publisher;

    /** @var Json */
    private $serializer;

    /** @var array */
    private $publishedIds = [];

    /** @var LoggerInterface  */
    private $logger;

    /**
     * @param PublisherInterface $publisher
     * @param Json $serializer
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        PublisherInterface $publisher,
        Json $serializer,
        LoggerInterface $loggerInterface
    ) {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->logger = $loggerInterface;
    }

    /**
     * Publishes the messages after validation
     *
     * Message template
     * [
     *   'correlation_id' => '<unique_message_id>',
     *   'event' => '<create|update|delete>',
     *   'entity_type' => '<customer|address>',
     *   'data'=> [...] //entity data
     * ]
     *
     * @param string $topic
     * @param array $message
     * @return boolean
     */
    public function publish(string $topic, array $message): bool
    {
        $correlationId = uniqid($message[MessageFormatter::EVENT_KEY], true);
        $message[MessageFormatter::CORRELATION_KEY] = $correlationId;

        $messageIdentifier = $this->generateMessageIdentifier($message);
        if ($this->validateMessageDuplication($messageIdentifier)) {
            $topicName = $topic;
            try {
                $serializedData = $this->serializer->serialize($message);
                $this->publisher->publish($topicName, $serializedData);
                $this->logger->info('event published'.$topic, $message);
                $this->publishedIds[] = $messageIdentifier;
            } catch (\Exception $e) {
                $this->handlePublishErrors($e, $message);
            }
        } else {
            $this->logger->info('Duplicate message was not published: "' . $messageIdentifier . '"');
        }
        return true;
    }

    /**
     * Handle exceptions while publishing events
     *
     * @param \Exception $exception
     * @param array $message
     * @return bool
     */
    private function handlePublishErrors(\Exception $exception, array $message): bool
    {
        //TODO Persist all the events that failed
        $this->logger->error($exception->getMessage(), $message);
        return false;
    }

    /**
     * Checks if the event is being published more than once
     *
     * @param string $messageIdentifier
     * @return bool
     */
    private function validateMessageDuplication(string $messageIdentifier): bool
    {
        return !in_array($messageIdentifier, $this->publishedIds);
    }

    /**
     * Generate a unique identifier for a message based on message attributes
     *
     * @param array $message
     * @return string
     */
    private function generateMessageIdentifier(array $message): string
    {
        $entityId = $message[MessageFormatter::DATA_KEY]['id'] ?? 0;
        return $message[MessageFormatter::EVENT_KEY]
            . '-' . $message[MessageFormatter::ENTITY_TYPE_KEY]
            . '-' . $entityId;
    }
}
