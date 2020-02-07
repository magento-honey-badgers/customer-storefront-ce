<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\Helper\MessageFormatter;
use Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\Helper\EventPublisher;

/**
 * Customer address storefront publisher
 */
class CustomerAddressStorefrontPublisherPlugin
{
    /** @var MessageFormatter  */
    private $messageFormatter;

    /** @var EventPublisher  */
    private $eventPublisher;

    private const ENTITY_TYPE = 'address';

    private const SAVE_EVENT = 'save';

    private const UPDATE_EVENT = 'update';

    private const DELETE_EVENT = 'delete';

    private const ADDRESS_SAVE_TOPIC = 'customer.monolith.connector.address.save';

    private const ADDRESS_DELETE_TOPIC = 'customer.monolith.connector.address.delete';

    /**
     * @param MessageFormatter $messageFormatter
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        MessageFormatter $messageFormatter,
        EventPublisher $eventPublisher
    ) {
        $this->messageFormatter = $messageFormatter;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * Plugin for publishing updated customer ids to the queue
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterface $address
     * @param AddressInterface $addressInput
     * @return AddressInterface
     */
    public function afterSave(
        AddressRepositoryInterface $addressRepository,
        AddressInterface $address,
        AddressInterface $addressInput
    ) {
        $addressId = $address->getId();
        $event = !$addressInput->getId() ? self::SAVE_EVENT : self::UPDATE_EVENT;
        $message = $this->messageFormatter->formatEventData(self::ENTITY_TYPE, $event, ['id' => $addressId]);
        $this->eventPublisher->publish(self::ADDRESS_SAVE_TOPIC, $message);
        return $address;
    }

    /**
     * Publishes an event after address delete
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param boolean $result
     * @param AddressInterface $address
     * @return mixed
     */
    public function afterDelete(AddressRepositoryInterface $addressRepository, $result, AddressInterface $address)
    {
        $addressId = $address->getId();
        $message = $this->messageFormatter->formatEventData(
            self::ENTITY_TYPE,
            self::DELETE_EVENT,
            ['id'=> $addressId]
        );
        $this->eventPublisher->publish(self::ADDRESS_DELETE_TOPIC, $message);
        return $result;
    }

    /**
     * Published an event after address is deleted by ID
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param boolean $result
     * @param int $addressId
     * @return mixed
     */
    public function afterDeleteById(AddressRepositoryInterface $addressRepository, $result, $addressId)
    {
        $message = $this->messageFormatter->formatEventData(
            self::ENTITY_TYPE,
            self::DELETE_EVENT,
            ['id'=> $addressId]
        );
        $this->eventPublisher->publish(self::ADDRESS_DELETE_TOPIC, $message);
        return $result;
    }
}