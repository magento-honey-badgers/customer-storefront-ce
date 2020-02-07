<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\Helper;

/**
 * Format Event Data
 */
class MessageFormatter
{
    /**
     * Format Customer Event Message
     *
     * @param string $entityType
     * @param string $event
     * @param array $data
     * @return array
     */
    public function formatEventData(string $entityType, string $event, array $data)
    {
        $message = [];
        $message['entity_type'] = $entityType;
        $message['event']=$event;
        $message['data']=$data;
        return $message;
    }
}
