<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Queue\Consumer;

use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Consume messages for customer entity changes and apply changes on storefront data
 */
class Customer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param LoggerInterface $logger
     * @param CustomerInterfaceFactory $customerFactory
     * @param SerializerInterface $serializer
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerInterfaceFactory $customerFactory,
        SerializerInterface $serializer,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->customerFactory = $customerFactory;
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Handle Customer Save
     *
     * @param string $incomingMessage
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function handleCustomerSave(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $customer = $this->customerFactory->create(['data' => $incomingMessageArray['data']]);
        $customer->setCreatedIn("monolith");
        $this->customerRepository->save($customer);
    }

    /**
     * Handle Customer Delete
     *
     * @param string $incomingMessage
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handleCustomerDelete(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $this->customerRepository->deleteById((int)$incomingMessageArray['data']['id']);
        $this->logger->info('Customer Deleted', [$incomingMessage]);
    }

    public function handleCustomerUpdateForId(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $customer = $this->customerFactory->create(['data' => $incomingMessageArray['data']]);
        $customer->setCreatedIn("monolith");
        $this->customerRepository->updateId($customer);
        $this->logger->info('Customer Id Updated', [$incomingMessage]);
    }
}
