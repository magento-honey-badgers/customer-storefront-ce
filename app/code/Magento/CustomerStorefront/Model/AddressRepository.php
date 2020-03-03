<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefront\Model;

use Magento\CustomerStorefrontApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontApi\Api\AddressRepositoryInterface;
use Magento\CustomerStorefront\Model\Storage\Address as AddressStorage;

/**
 * Repository for customer address.
 */
class AddressRepository implements AddressRepositoryInterface
{
    /**
     * @var AddressStorage
     */
    private $addressStorage;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param AddressStorage $addressStorage
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        AddressStorage $addressStorage,
        CustomerRepository $customerRepository
    ) {
        $this->addressStorage = $addressStorage;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Save customer address.
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(AddressInterface $address): AddressInterface
    {
        $this->addressStorage->persist($address);

        if (($address->isDefaultShipping() || $address->isDefaultBilling()) && $address->getCustomerId()) {
            $customer = $this->customerRepository->getById($address->getCustomerId());
            if ($address->isDefaultShipping()) {
                $customer->setDefaultShipping($address->getId());
            }
            if ($address->isDefaultBilling()) {
                $customer->setDefaultBilling($address->getId());
            }
            $this->customerRepository->save($customer);
        }

        //TODO this won't work when creating an address in storefront (new address won't have an ID)
        return $this->getById($address->getId());
    }

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $addressId): bool
    {
        return $this->addressStorage->deleteById($addressId);
    }

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $addressId): AddressInterface
    {
        return $this->addressStorage->fetchByAddressId($addressId);
    }

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param int $customerId
     * @return \Magento\CustomerStorefrontApi\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(int $customerId): array
    {
        return $this->addressStorage->fetchAddressesByCustomerId($customerId);
    }

    /**
     * Delete customer address.
     *
     * @param AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(AddressInterface $address): bool
    {
        return $this->addressStorage->deleteById($address->getId());
    }
}
