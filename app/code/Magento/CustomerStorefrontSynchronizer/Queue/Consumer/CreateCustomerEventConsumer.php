<?php
/**
 * Created by PhpStorm.
 * User: pganapat
 * Date: 2/20/20
 * Time: 10:55 AM
 */

namespace Magento\CustomerStorefrontSynchronizer\Queue\Consumer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\CustomerStorefrontSynchronizer\Queue\Helper\EventValidator;

class CreateCustomerEventConsumer
{

    public function __construct(
        TimezoneInterface $timezone,
        Json $serializer,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        EventValidator $eventValidator
    ) {
        $this->timezone = $timezone;
        $this->serializer = $serializer;
        $this->customerFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->eventValidator = $eventValidator;
    }

    public function handleCreateCustomer(string $incomingMessage)
    {
        $data = $this->serializer->unserialize($incomingMessage);
        $customerData = $data['data'];
        $customer = $this->customerFactory->create(['data' => $customerData]);
        $customer->setCreatedIn('Store Front');
        $validEvent = true;
        try
        {
            $existingCustomerRecord = $this->customerRepository->get($customer->getEmail());
            $lastModified = $existingCustomerRecord->getUpdatedAt();
            $lastModifiedTs = $this->timezone->date(new \DateTime($lastModified))->getTimestamp();
            $validEvent = $this->eventValidator->validate($data, $lastModifiedTs);
            $this->logger->info('lastmodified ts', [$lastModifiedTs]);
        } catch (NoSuchEntityException $e) {
            $this->logger->info('Validation Skipped - saving customer');
        }
        $this->logger->info('Received CustomerCreate', $customer->__toArray());
        if ($validEvent) {
            $this->logger->info('Event ts', [$data['event_timestamp']]);
            $this->logger->info('Validation Succeeded - saving customer');
            $this->customerRepository->save($customer);
        } else {
            $this->logger->info('Validation Failed - saving customer');
        }
    }
}
