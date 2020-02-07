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
     * @param string $topic
     * @param array $message
     * @return boolean
     */
    public function publish(string $topic, array $message) :bool
    {
        $correlationId = uniqid($message['event'], true);
        $message['correlation_id'] = $correlationId;
        $entityId=$this->getEntityIdfromMessage($message);
        if ($this->validateMessageDuplication($entityId)) {
            $topicName = $topic;
            try {
                $serializedData = $this->serializer->serialize($message);
                $this->publisher->publish($topicName, $serializedData);
                $this->logger->info('event published', $message);
                $this->publishedIds[] = $entityId;
            } catch (\Exception $e) {
                $this->handlePublishErrors($e, $message);
            }
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
     * @param int $entityId
     * @return bool
     */
    private function validateMessageDuplication(int $entityId): bool
    {
        if (isset($entityId)) {
            return !in_array($entityId, $this->publishedIds);
        }
        return true;
    }

    /**
     * Get Entity Id
     *
     * @param array $message
     * @return int
     */
    private function getEntityIdfromMessage(array $message)
    {
        if (isset($message['data']) && isset($message['data']['id'])) {
            $entityId = $message['data']['id'];
            return (int)$entityId;
        }
        return null;
    }
}
