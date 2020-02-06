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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

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
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CustomerValidator $validator,
        CustomerInterfaceFactory $customerInterfaceFactory,
        Json $serializer
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->validator = $validator;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->serializer = $serializer;
    }

    public function fetchById(int $customerId)
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

    public function persist(CustomerInterface $customer)
    {
        if ($this->validator->validate($customer)) {
            if ($this->customerExists($customer)) {
                $this->doUpdate($customer);
            } else {
                $this->doInsert($customer);
            }
        } else {
            throw new \InvalidArgumentException("Customer invalid. TODO make this better");
        }
        //TODO retrieve customer using storefront_customer_id
    }

    private function doInsert(CustomerInterface $customer)
    {
        $customerDocument = $this->serializer->serialize($customer->__toArray());

        $this->getConnection()->insert(self::TABLE, ['customer_document' => $customerDocument]);
    }

    private function doUpdate(CustomerInterface $customer)
    {
        $existingCustomer = $this->fetchById($customer->getId());

        //TODO make a diff class that does this well
        $existingCustomerArray = $existingCustomer->__toArray();
        $updateCustomerArray = $customer->__toArray();

        foreach ($updateCustomerArray as $key => $value) {
            if ($existingCustomerArray[$key] != $value) {
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

    private function customerExists(CustomerInterface $customer)
    {
        $bind = ['customer_id' => $customer->getId()];
        $select = $this->getConnection()->select()->from(self::TABLE);
        $select->where('customer_id = :customer_id');

        $result = $this->getConnection()->fetchOne($select, $bind);
        return !empty($result);
    }

    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

}
