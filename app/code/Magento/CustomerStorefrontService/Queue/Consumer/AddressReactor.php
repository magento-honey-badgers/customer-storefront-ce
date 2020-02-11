<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Queue\Consumer;

use Psr\Log\LoggerInterface;
use Magento\CustomerStorefrontService\Model\Data\Mapper\AddressMapper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CustomerStorefrontService\Model\AddressRepository;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontService\Model\Storage\Address as AddressStorage;

/**
 * Handle address save messages
 */
class AddressReactor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $addressRepository;

    private $address;

    private $addressMapper;

    private $serializer;

    private $addressStorage;

    /**
     * @param LoggerInterface $logger
     * @param AddressRepository $addressRepository
     * @param AddressInterface $address
     * @param AddressMapper $addressMapper
     * @param SerializerInterface $serializer
     * @param AddressStorage $addressStorage
     */
    public function __construct(
        LoggerInterface $logger,
        AddressRepository $addressRepository,
        AddressInterface $address,
        AddressMapper $addressMapper,
        SerializerInterface $serializer,
        AddressStorage $addressStorage
    ) {
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->address = $address;
        $this->addressMapper = $addressMapper;
        $this->serializer = $serializer;
        $this->addressStorage = $addressStorage;
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
        /**
         * @var $customer AddressInterface
         */
        $address = $this->addressMapper->mapAddressData($incomingMessageArray);
        // TODO clean up Address repo and move storage there
        $this->addressStorage->persist($address);
//        $this->addressRepository->save($address);
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
        $this->addressStorage->delete((int)$incomingMessageArray['data']['id']);
        $this->logger->info('Address Deleted', [$incomingMessage]);
    }
}

