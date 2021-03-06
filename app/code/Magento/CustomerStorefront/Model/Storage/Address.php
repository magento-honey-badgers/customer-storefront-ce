<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefront\Model\Storage;

use Magento\CustomerStorefrontApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontApi\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\CustomerStorefront\Model\Storage\Customer as CustomerStorage;

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
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var CustomerStorage
     */
    private $customerStorage;

    /**
     * @param ResourceConnection $resourceConnection
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param Json $serializer
     * @param CustomerStorage $customerStorage
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
     * @return AddressInterface[]
     */
    public function fetchAddressesByCustomerId(int $customerId): array
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
    public function fetchByAddressId(int $addressId): AddressInterface
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
    public function deleteById(int $addressId): bool
    {
        if (!$this->addressExists($addressId)) {
            throw NoSuchEntityException::singleField('address_id', $addressId);
        }
        return $this->doDelete($addressId);
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
            $this->getConnection()->beginTransaction();
            $address = $this->fetchByAddressId($addressId);
            if ($address->getCustomerId()) {
                $customer = $this->customerStorage->fetchById($address->getCustomerId());
                if (($customer->getDefaultBilling() == $address->getId())) {
                    $customer->setDefaultBilling(0);
                }
                if (($customer->getDefaultShipping() == $address->getId())) {
                    $customer->setDefaultShipping(0);
                }
                $this->customerStorage->persist($customer);
            }
            $this->getConnection()->delete(
                self::TABLE,
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
            ['customer_address_id = ?' => $addressId]
        );
    }

    /**
     * Check if address exists
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
     * Get connection adapter
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }
}
