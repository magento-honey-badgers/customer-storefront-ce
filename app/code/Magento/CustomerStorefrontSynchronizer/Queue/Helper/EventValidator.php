<?php
/**
 * Created by PhpStorm.
 * User: pganapat
 * Date: 2/21/20
 * Time: 1:55 PM
 */

namespace Magento\CustomerStorefrontSynchronizer\Queue\Helper;

class EventValidator
{
    private const SAVE_EVENT = 'save';

    private const UPDATE_EVENT = 'update';

    private const DELETE_EVENT = 'delete';

    private const CREATE_EVENT = 'create';

    public function validate(array $eventMetadata, int $lastUpdatedAt): bool
    {
        $eventType = $eventMetadata['event'];
        $eventTimestamp = $eventMetadata['event_timestamp'];

        switch ($eventType) {
            case self::UPDATE_EVENT:
                return $eventTimestamp > $lastUpdatedAt;
            case self::SAVE_EVENT:
                return $lastUpdatedAt == null;
            case self::CREATE_EVENT:
                return $eventTimestamp > $lastUpdatedAt;
            case self::DELETE_EVENT:
                return $eventTimestamp > $lastUpdatedAt;
        }
    }
}
