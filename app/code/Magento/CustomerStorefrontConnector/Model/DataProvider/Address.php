<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer Address data provider
 */
class Address
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressTransformer
     */
    private $addressTransformer;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressTransformer $addressTransformer
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressTransformer $addressTransformer
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressTransformer = $addressTransformer;
    }

    /**
     * Fetch Address data
     *
     * @param int $addressId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getData(int $addressId): array
    {
        $address = $this->addressRepository->getById($addressId);

        return $this->addressTransformer->toArray($address);
    }
}
