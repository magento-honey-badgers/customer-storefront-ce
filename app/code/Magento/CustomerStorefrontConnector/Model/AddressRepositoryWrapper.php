<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\CustomerStorefrontConnector\Adapter\Rest\Client;
use Magento\CustomerStorefrontConnector\Model\Data\AddressTransformer;

/**
 * AddressRepository using REST API
 */
class AddressRepositoryWrapper
{
    private const GET_URI = '/rest/V1/customers/addresses/%s';

    /**
     * @var Client
     */
    private $restClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AddressTransformer
     */
    private $dataTransformer;

    /**
     * @param Client $client
     * @param SerializerInterface $serializer
     * @param AddressTransformer $addressTransformer
     */
    public function __construct(
        Client $client,
        SerializerInterface $serializer,
        AddressTransformer $addressTransformer
    ) {
        $this->restClient = $client;
        $this->serializer = $serializer;
        $this->dataTransformer = $addressTransformer;
    }

    /**
     * Get address data by address ID
     *
     * @param int $addressId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById(int $addressId): array
    {
        $route = sprintf(self::GET_URI, $addressId);
        $response = $this->restClient->sendRequest($route);

        if (200 == $response['status']) {
            $addressData = $this->serializer->unserialize($response['body']);
            return $this->dataTransformer->toArray($addressData);
        } elseif (404 == $response['status']) {
            throw new NoSuchEntityException(__('No such entity with addressId = %1', $addressId));
        } else {
            throw new LocalizedException(__('Could not fetch data for address with id: ' . $addressId));
        }
    }
}
