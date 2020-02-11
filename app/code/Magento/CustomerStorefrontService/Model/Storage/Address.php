<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Storage;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\CustomerStorefrontService\Model\Storage\Customer as CustomerStorage;

/**
 * Address storage
 */
class Address
{
    const TABLE = 'storefront_customer_address';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerValidator
     */
    private $validator;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var Json
     */
    private $serializer;

    private $customerStorage;

    /**
     * @param ResourceConnection $resourceConnection
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param Json $serializer
     * @param Customer $customerStorage
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AddressInterfaceFactory $addressInterfaceFactory,
        Json $serializer,
        CustomerStorage $customerStorage
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->serializer = $serializer;
        $this->customerStorage = $customerStorage;
    }

    /**
     * Fetch all addresses belonging to a customer
     *
     * @param int $customerId
     * @return array
     */
    public function fetchAddressesByCustomerId(int $customerId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(self::TABLE)
            ->where('customer_id = :customer_id');
        $bind = ['customer_id' => $customerId];

        $result = $this->getConnection()->fetchAssoc($select, $bind);

        $addresses = [];
        foreach ($result as $rowItem) {
            $addressModel = $this->addressInterfaceFactory->create([
                'data' => $this->serializer->unserialize(
                    $rowItem['customer_address_document']
                ) //todo make this better
            ]);
            $addresses[] = $addressModel;
        }
        return $addresses;
    }

    /**
     * Fetch address based on id
     *
     * @param int $addressId
     * @return AddressInterface
     * @throws NoSuchEntityException
     */
    public function fetchByAddressId(int $addressId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(self::TABLE)
            ->where('customer_address_id = :address_id');
        $bind = ['address_id' => $addressId];

        $result = $this->getConnection()->fetchAssoc($select, $bind);
        if (!$result) {
            throw NoSuchEntityException::singleField('address_id', $addressId);
        }

        $addressModel = $this->addressInterfaceFactory->create([
            'data' => $this->serializer->unserialize(
                reset($result)['customer_address_document']
            )
        ]);

        return $addressModel;
    }

    /**
     * Save an address
     *
     * @param AddressInterface $address
     */
    public function persist(AddressInterface $address)
    {
        if ($this->addressExists($address->getId())) {
            $this->doUpdate($address);
        } else {
            $this->doInsert($address);
        }
    }

    /**
     * Delete an address
     *
     * @param int $addressId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete(int $addressId): bool
    {
        if (!$this->addressExists($addressId)) {
            throw NoSuchEntityException::singleField('address_id', $addressId);
        }
        return $this->doDelete($addressId);
    }

    /**
     * Delete all addresses belonging to a customer
     *
     * @param int $customerId
     */
    public function deleteAllAddresses(int $customerId)
    {
        $this->getConnection()->delete(
            self::TABLE,
            ['customer_id = ?' => $customerId]
        );
    }

    /**
     * Perform address delete
     *
     * @param int $addressId
     * @return bool
     * @throws \Exception
     */
    private function doDelete(int $addressId): bool
    {
        try {
            $address = $this->fetchByAddressId($addressId);
            $customer = $this->customerStorage->fetchById($address->getCustomerId());
            if (($customer->getDefaultBilling() == (string)$address->getId())) {
                $customer->setDefaultBilling("");
            }
            if (($customer->getDefaultShipping() == (string)$address->getId())) {
                $customer->setDefaultShipping("");
            }
            $this->getConnection()->beginTransaction();
            $this->customerStorage->persist($customer);
            $this->getConnection()->delete(
                'storefront_customer_address',
                ['customer_address_id = ?' => $addressId]
            );
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return true;
    }

    /**
     * Perform address insert
     *
     * @param AddressInterface $address
     */
    private function doInsert(AddressInterface $address)
    {
        $addressDocument = $this->serializer->serialize($address->__toArray());

        $this->getConnection()->insert(
            self::TABLE,
            [
                'customer_address_document' => $addressDocument
            ]
        );
    }

    /**
     * Perform address update
     *
     * @param AddressInterface $address
     */
    private function doUpdate(AddressInterface $address)
    {
        $addressId = $address->getId();
        $addressDocument = $this->serializer->serialize($address->__toArray());
        $this->getConnection()->update(
            self::TABLE,
            ['customer_address_document' => $addressDocument],
            ['customer_address_id' => $addressId]
        );
    }

    /**
     * Checks address exists
     *
     * @param int $addressId
     * @return bool
     */
    private function addressExists(int $addressId): bool
    {
        $bind = ['customer_address_id' => $addressId];
        $select = $this->getConnection()->select()->from(self::TABLE);
        $select->where('customer_address_id = :customer_address_id');

        $result = $this->getConnection()->fetchOne($select, $bind);
        return !empty($result);
    }

    /**
     * Connection helper
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

}

