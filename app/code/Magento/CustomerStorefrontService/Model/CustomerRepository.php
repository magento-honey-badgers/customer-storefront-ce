<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

/**
 * Customer repository
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getById(int $customerId): CustomerInterface
    {
    }

    /**
     * @inheritDoc
     */
    public function save(CustomerInterface $customer): CustomerInterface
    {
    }
}
