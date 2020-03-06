<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerSynchronizer\Model\ResourceModel\Plugin\Helper;

/**
 * Format Event Data
 */
class MessageFormatter
{
    const ENTITY_TYPE_KEY = 'entity_type';

    const EVENT_KEY = 'event';

    const DATA_KEY = 'data';

    const CORRELATION_KEY = 'correlation_id';

    /**
     * Format Customer Event Message
     *
     * @param string $entityType
     * @param string $event
     * @param array $data
     * @return array
     */
    public function formatEventData(string $entityType, string $event, array $data): array
    {
        return [
            self::ENTITY_TYPE_KEY => $entityType,
            self::EVENT_KEY => $event,
            self::DATA_KEY => $data
        ];
    }
}
