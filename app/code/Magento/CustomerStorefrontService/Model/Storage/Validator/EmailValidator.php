<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Storage\Validator;

use Magento\CustomerStorefrontService\Model\Storage\Customer;
use Magento\CustomerStorefrontService\Model\Storage\ValidatorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

class EmailValidator implements ValidatorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resouce)
    {
        $this->resource = $resouce;
    }

    public function validate(CustomerInterface $customer): bool
    {
        return $this->isEmailAvailable($customer);
    }

    private function isEmailAvailable(CustomerInterface $customer)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(Customer::TABLE)
            ->where('JSON_EXTRACT(customer_document, "$.email") = :email');

        $bind = ['email' => $customer->getEmail()];

        if ($customer->getId()) {
            $select->where('customer_id != :customer_id');
            $bind['customer_id'] = $customer->getId();
        }

        $check = $connection->fetchOne($select, $bind);

        return empty($check);
    }
}
