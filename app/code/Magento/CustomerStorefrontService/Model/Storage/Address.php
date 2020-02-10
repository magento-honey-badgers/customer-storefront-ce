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

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AddressInterfaceFactory $addressInterfaceFactory,
        Json $serializer
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->serializer = $serializer;
    }

    public function fetchByCustomerId(int $customerId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(self::TABLE)
            ->where('customer_id = :customer_id');
        $bind = ['customer_id' => $customerId];

        $result = $this->getConnection()->fetchAssoc($select, $bind);
        if (!$result) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }

        $resultSet = [];
        foreach ($result as $rowItem) {
            $addressModel = $this->addressInterfaceFactory->create([
                'data' => $this->serializer->unserialize(
                    $rowItem['customer_address_document']
                ) //todo make this better
            ]);
            $resultSet[] = $addressModel;
        }
        return $resultSet;
    }

    public function persist(AddressInterface $address)
    {
        if (true) {
            if ($this->customerExists($address->getCustomerId())) {
//                $this->doUpdate($address);
                $this->doInsert($address);
            }
        } else {
            throw new \InvalidArgumentException("Customer invalid. TODO make this better");
        }
        //TODO retrieve customer using storefront_customer_id
    }

    private function doInsert(AddressInterface $address)
    {
        $customerId = $address->getCustomerId();
        $addressDocument = $this->serializer->serialize($address->__toArray());

        $this->getConnection()->insert(
            self::TABLE,
            [
                'customer_address_document' => $addressDocument,
                'customer_id' => $customerId
            ]
        );
    }

    private function doUpdate(AddressInterface $address)
    {
        $existingCustomer = $this->fetchById($address->getId());

        //TODO make a diff class that does this well
        $existingCustomerArray = $existingCustomer->__toArray();
        $updateCustomerArray = $address->__toArray();

        foreach ($updateCustomerArray as $key => $value) {
            if ($existingCustomerArray[$key] != $value) {
                $existingCustomer->setData($key, $value);
            }
        }

        $updateCustomerDocument = $this->serializer->serialize($existingCustomer->__toArray());

        $this->getConnection()->update(
            self::TABLE,
            ['customer_document' => $updateCustomerDocument],
            ['customer_row_id = ?' => $existingCustomer->getId()]
        );
    }

    private function customerExists(int $customerId)
    {
        return true;
    }

    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

}
