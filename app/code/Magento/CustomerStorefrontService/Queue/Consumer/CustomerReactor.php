<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Queue\Consumer;

use Psr\Log\LoggerInterface;
use Magento\CustomerStorefrontService\Model\Data\Mapper\CustomerMapper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CustomerStorefrontService\Model\CustomerRepository;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

/**
 * Handle customer save messages
 */
class CustomerReactor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $customerMapper;

    private $serializer;

    private $customerRepository;

    /**
     * @param LoggerInterface $logger
     * @param CustomerMapper $customerMapper
     * @param SerializerInterface $serializer
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerMapper $customerMapper,
        SerializerInterface $serializer,
        CustomerRepository $customerRepository
    ) {
        $this->logger = $logger;
        $this->customerMapper = $customerMapper;
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Send address data to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function handleCustomerSave(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        /**
         * @var $customer CustomerInterface
         */
        $customer = $this->customerMapper->mapCustomerData($incomingMessageArray);
        $this->customerRepository->save($customer);
    }

    /**
     * Forward deleted address ID to storefront service queue
     *
     * @param string $incomingMessage
     */
    public function handleCustomerDelete(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
    }
}
