<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\Helper\MessageFormatter;
use Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\Helper\EventPublisher;

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

    private const SAVE_EVENT = 'save';

    private const DELETE_EVENT = 'delete';

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
     * @return CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function afterSave(CustomerRepository $customerRepository, CustomerInterface $customer)
    {
        $customerId = $customer->getId();
        $message = $this->messageFormatter->formatEventData(self::ENTITY_TYPE, self::SAVE_EVENT, ['id'=> $customerId]);
        $this->eventPublisher->publish('customer.monolith.connector.saveCustomer', $message);
        return $customer;
    }

    /**
     * Plugin for publishing deleted customer Id to the queue
     *
     * @param CustomerRepository $customerRepository
     * @param boolean $result
     * @param CustomerInterface $customer
     * @return mixed
     */
    public function afterDelete(CustomerRepository $customerRepository, $result, CustomerInterface $customer)
    {
        $customerId = $customer->getId();
        $message = $this->messageFormatter->formatEventData(
            self::ENTITY_TYPE,
            self::DELETE_EVENT,
            ['id'=> $customerId]
        );
        $this->eventPublisher->publish('customer.monolith.connector.deleteCustomer', $message);
        return $result;
    }

    /**
     * Plugin for publishing deleted customer Id to the queue
     *
     * @param CustomerRepository $customerRepository
     * @param boolean $result
     * @param int $customerId
     * @return mixed
     */
    public function afterDeleteById(CustomerRepository $customerRepository, $result, $customerId)
    {
        $message = $this->messageFormatter->formatEventData(
            self::ENTITY_TYPE,
            self::DELETE_EVENT,
            ['id'=> $customerId]
        );
        $this->eventPublisher->publish('customer.monolith.connector.deleteCustomer', $message);
        return $result;
    }
}
