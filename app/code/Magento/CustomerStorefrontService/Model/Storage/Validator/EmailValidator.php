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

/**
 * Validate that email address is correct format and available
 */
class EmailValidator implements ValidatorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var null|string
     */
    private $errorMessage = null;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Validate the customer email address
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function validate(CustomerInterface $customer): bool
    {
        $this->errorMessage = null;

        //TODO validate email format

        return $this->isEmailAvailable($customer);
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Check if email address is available
     *
     * @param CustomerInterface $customer
     * @return bool
     */
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

        if (empty($check)) {
            return true;
        } else {
            $this->errorMessage = 'A customer with the same email address already exists.';
            return false;
        }
    }
}
