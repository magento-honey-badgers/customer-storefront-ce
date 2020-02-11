<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Queue\Consumer;

use Psr\Log\LoggerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CustomerStorefrontServiceApi\Api\AddressRepositoryInterface;

/**
 * Handle address save messages
 */
class AddressReactor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param LoggerInterface $logger
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        SerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->serializer = $serializer;
    }

    /**
     * Handle Address save
     *
     * @param string $incomingMessage
     */
    public function handleAddressSave(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $address = $this->addressFactory->create(['data' => $incomingMessageArray['data']]);
        $this->addressRepository->save($address);
        $this->logger->info('Address Saved', [$address->getId()]);
    }

    /**
     * Handle Address Deletion
     *
     * @param string $incomingMessage
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleAddressDelete(string $incomingMessage): void
    {
        $this->logger->info('Message Received', [$incomingMessage]);
        $incomingMessageArray = $this->serializer->unserialize($incomingMessage);
        $this->addressRepository->deleteById((int)$incomingMessageArray['data']['id']);
        $this->logger->info('Address Deleted', [$incomingMessage]);
    }
}

