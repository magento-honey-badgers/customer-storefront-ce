<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model;

use Magento\CustomerStorefrontConnector\Adapter\Rest\Client;
use Magento\CustomerStorefrontConnector\Model\Data\CustomerTransformer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * CustomerRepository using REST API
 */
class CustomerRepositoryWrapper
{
    private const GET_URI = '/rest/V1/customers/%s';

    /**
     * @var Client
     */
    private $restClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CustomerTransformer
     */
    private $dataTransformer;

    /**
     * @param Client $client
     * @param SerializerInterface $serializer
     * @param CustomerTransformer $customerTransformer
     */
    public function __construct(
        Client $client,
        SerializerInterface $serializer,
        CustomerTransformer $customerTransformer
    ) {
        $this->restClient = $client;
        $this->serializer = $serializer;
        $this->dataTransformer = $customerTransformer;
    }

    /**
     * Get customer data by customer id
     *
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById(int $customerId): array
    {
        $route = sprintf(self::GET_URI, $customerId);
        $response = $this->restClient->sendRequest($route);

        if (200 == $response['status']) {
            $customerData = $this->serializer->unserialize($response['body']);
            return $this->dataTransformer->toArray($customerData);
        } elseif (404 == $response['status']) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        } else {
            throw new LocalizedException(__('Could not fetch data for customer with id: ' . $customerId));
        }
    }
}
