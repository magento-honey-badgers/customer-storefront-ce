<?php
/**
 * Created by PhpStorm.
 * User: pganapat
 * Date: 2/19/20
 * Time: 2:58 PM
 */

namespace Magento\CustomerStorefrontService\Model\Plugin;

use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface as CustomerInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Plugin for Customer Updates
 */
class CustomerPublisherPlugin
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /** @var Json */
    private $serializer;

    /**
     * @param PublisherInterface $publisher
     * @param Json $serializer
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        PublisherInterface $publisher,
        Json $serializer,
        LoggerInterface $logger,
        TimezoneInterface $timezone
    ) {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * Plugin for publishing customer updates
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customer
     * @param CustomerInterface $customerInput
     * @return CustomerInterface
     */
    public function afterSave(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer,
        CustomerInterface $customerInput
    ) {
        if ($customerInput->getCreatedIn() != 'monolith') {
            $this->publisher->publish(
                'customer.service.monolith.customer.save',
                $this->prepareMessagePacket($customer)
            );
        }
        return $customer;
    }

    /**
     * Prepare message packet for publishing
     *
     * @param CustomerInterface $customer
     * @return bool|false|string
     */
    private function prepareMessagePacket(CustomerInterface $customer)
    {
        $data = [];
        $data['data'] = $customer->__toArray();
        $data['event'] = 'CREATE';
        $data['entity_type'] = 'customer';
        $data['event_timestamp'] = $this->timezone->date()->getTimestamp();
        return $this->serializer->serialize($data);
    }
}
