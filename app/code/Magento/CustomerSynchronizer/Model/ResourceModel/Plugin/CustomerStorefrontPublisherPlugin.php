<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\CustomerSynchronizer\Model\ResourceModel\Plugin\Helper\MessageFormatter;
use Magento\CustomerSynchronizer\Model\ResourceModel\Plugin\Helper\EventPublisher;

/**
 * Customer storefront publisher
 */
class CustomerStorefrontPublisherPlugin
{
    /** @var MessageFormatter  */
    private $messageFormatter;

    /** @var EventPublisher  */
    private $eventPublisher;

    private const ENTITY_TYPE = 'customer';

    private const SAVE_EVENT = 'create';

    private const UPDATE_EVENT = 'update';

    private const DELETE_EVENT = 'delete';

    private const CUSTOMER_SAVE_TOPIC = 'customer.monolith.messageBroker.customer.save';

    private const CUSTOMER_DELETE_TOPIC = 'customer.monolith.messageBroker.customer.delete';

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
     * Plugin for publishing updated customer id to the queue
     *
     * @param CustomerRepository $customerRepository
     * @param CustomerInterface $customer
     * @param CustomerInterface $customerInput
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CustomerRepository $customerRepository,
        CustomerInterface $customer,
        CustomerInterface $customerInput
    ) {
        $customerId = $customer->getId();
        $event = !$customerInput->getId() ? self::SAVE_EVENT : self::UPDATE_EVENT;
        $message = $this->messageFormatter->formatEventData(self::ENTITY_TYPE, $event, ['id'=> $customerId]);
        $this->eventPublisher->publish(self::CUSTOMER_SAVE_TOPIC, $message);
        return $customer;
    }

    /**
     * Plugin for publishing deleted customer Id to the queue
     *
     * @param CustomerRepository $customerRepository
     * @param boolean $result
     * @param CustomerInterface $customer
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(CustomerRepository $customerRepository, $result, CustomerInterface $customer)
    {
        $customerId = $customer->getId();
        $message = $this->messageFormatter->formatEventData(
            self::ENTITY_TYPE,
            self::DELETE_EVENT,
            ['id'=> $customerId]
        );
        $this->eventPublisher->publish(self::CUSTOMER_DELETE_TOPIC, $message);
        return $result;
    }

    /**
     * Plugin for publishing deleted customer Id to the queue
     *
     * @param CustomerRepository $customerRepository
     * @param boolean $result
     * @param int $customerId
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(CustomerRepository $customerRepository, $result, $customerId)
    {
        $message = $this->messageFormatter->formatEventData(
            self::ENTITY_TYPE,
            self::DELETE_EVENT,
            ['id'=> $customerId]
        );
        $this->eventPublisher->publish(self::CUSTOMER_DELETE_TOPIC, $message);
        return $result;
    }
}
