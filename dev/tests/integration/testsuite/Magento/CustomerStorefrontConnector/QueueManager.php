<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector;

use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Simple actions for working with queues
 */
class QueueManager
{
    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $connection = 'amqp';

    /**
     * @param QueueRepository $queueRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        QueueRepository $queueRepository,
        SerializerInterface $serializer
    ) {
        $this->queueRepository = $queueRepository;
        $this->serializer = $serializer;
    }

    /**
     * Consume a message off the queue
     *
     * @param string $queueName
     * @return string
     */
    public function popMessage(string $queueName): string
    {
        $queue = $this->getQueue($queueName);
        $message = null;
        $loops = 10;
        while (!$message && $loops) {
            $loops--;
            $message = $queue->dequeue();
            if (empty($message)) {
                sleep(1);
            }
        }

        $payload = $this->serializer->unserialize($message->getBody());
        $queue->acknowledge($message);

        return $payload;
    }

    /**
     * Clean up queues by acknowledging all messages
     *
     * @param array $queueNames
     */
    public function cleanQueues(array $queueNames)
    {
        foreach ($queueNames as $queueName) {
            $queue = $this->getQueue($queueName);
            for ($i = 0; $i < 100; $i++) {
                $message = $queue->dequeue();
                if (empty($message)) {
                    break 1;
                }
                $queue->acknowledge($message);
            }
        }
    }

    /**
     * Get a queue by name
     *
     * @param string $queueName
     * @return QueueInterface
     */
    public function getQueue(string $queueName): QueueInterface
    {
        return $this->queueRepository->get($this->connection, $queueName);
    }
}
