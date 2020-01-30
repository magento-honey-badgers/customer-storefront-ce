<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Generate message for queue in proper format
 */
class MessageGenerator
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Generate message as associative array
     *
     * @param array $primaryData
     * @param array $metadata
     * @return array
     */
    public function generate(array $primaryData, array $metadata): array
    {
        $messageArray = $metadata;
        $messageArray['data'] = $primaryData;

        return $messageArray;
    }

    /**
     * Generate message as serialized json string
     *
     * @param array $primaryData
     * @param array $metadata
     * @return string
     */
    public function generateSerialized(array $primaryData, array $metadata): string
    {
        $messageArray = $this->generate($primaryData, $metadata);

        return $this->serializer->serialize($messageArray);
    }
}
