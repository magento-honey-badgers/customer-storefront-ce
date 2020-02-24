<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Storage;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Persistence layer for Customer model
 */
class Customer
{
    const TABLE = 'storefront_customer';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerValidator
     */
    private $validator;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param ResourceConnection $resourceConnection
     * @param CustomerValidator $validator
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param Json $serializer
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CustomerValidator $validator,
        CustomerInterfaceFactory $customerInterfaceFactory,
        Json $serializer
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->validator = $validator;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->serializer = $serializer;
    }

    /**
     * Fetch customer by id
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws NoSuchEntityException
     */
    public function fetchById(int $customerId): CustomerInterface
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

        $customerModel = $this->customerInterfaceFactory->create([
            'data' => $this->serializer->unserialize(reset($result)['customer_document']) //todo make this better
        ]);

        return $customerModel;
    }

    /**
     * Persist customer to database
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function persist(CustomerInterface $customer): CustomerInterface
    {
        if ($this->validator->validate($customer)) {
            if ($this->customerExists($customer)) {
                $this->doUpdate($customer);
            } else {
                $this->doInsert($customer);
            }
        } else {
            throw new InputException(__($this->validator->getErrorMessage()));
        }
        //TODO retrieve customer using storefront_customer_id
        return $customer;
    }

    /**
     * Delete customer from database
     *
     * @param CustomerInterface $customer
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete(CustomerInterface $customer): bool
    {
        if (empty($customer->getId()) || !$this->customerExists($customer)) {
            throw NoSuchEntityException::singleField('customerId', $customer->getId());
        }
        return $this->doDelete($customer);
    }

    /**
     * Delete customer from database based on customerId
     *
     * @param int $customerId
     * @return bool
     * @throws \Exception
     */
    public function deleteById(int $customerId): bool
    {
        return $this->doDeleteById($customerId);
    }

    public function updateId(CustomerInterface $customer): CustomerInterface
    {
        $updateCustomerDocument = $this->serializer->serialize($customer->__toArray());

        $this->getConnection()->update(
            self::TABLE,
            ['customer_document' => $updateCustomerDocument],
            ['email = ?' => $customer->getEmail()]
        );
        return $customer;
    }

    /**
     * Perform customer insert
     *
     * @param CustomerInterface $customer
     */
    private function doInsert(CustomerInterface $customer)
    {
        $customerDocument = $this->serializer->serialize($customer->__toArray());

        $this->getConnection()->insert(self::TABLE, ['customer_document' => $customerDocument]);
    }

    /**
     * Perform customer update
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function doUpdate(CustomerInterface $customer)
    {
        $existingCustomer = $this->fetchById($customer->getId());

        //TODO make a diff class that does this well
        $existingCustomerArray = $existingCustomer->__toArray();
        $updateCustomerArray = $customer->__toArray();

        foreach ($updateCustomerArray as $key => $value) {
            if (true) {
                $existingCustomer->setData($key, $value);
            }
        }

        $updateCustomerDocument = $this->serializer->serialize($existingCustomer->__toArray());

        $this->getConnection()->update(
            self::TABLE,
            ['customer_document' => $updateCustomerDocument],
            ['customer_id = ?' => $existingCustomer->getId()]
        );
    }

    /**
     * Perform customer delete
     *
     * @param CustomerInterface $customer
     * @return bool
     * @throws \Exception
     */
    private function doDelete(CustomerInterface $customer): bool
    {
        return $this->doDeleteById($customer->getId());
    }

    /**
     * Perform Customer Delete by Id
     *
     * @param int $customerID
     * @return bool
     * @throws \Exception
     */
    private function doDeleteById(int $customerID): bool
    {
        $this->getConnection()->beginTransaction();
        try {
            //delete all addresses
            $this->getConnection()->delete(Address::TABLE, ['customer_id = ?' => $customerID]);
            $this->getConnection()->delete(self::TABLE, ['customer_id = ?' => $customerID]);
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return true;
    }

    /**
     * Check if customer already exists
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function customerExists(CustomerInterface $customer): bool
    {
        $bind = ['customer_id' => $customer->getId()];
        $select = $this->getConnection()->select()->from(self::TABLE);
        $select->where('customer_id = :customer_id');

        $result = $this->getConnection()->fetchOne($select, $bind);
        return !empty($result);
    }

    /**
     * Get database connection
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }
}
