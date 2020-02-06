<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Storage;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

class CustomerValidator implements ValidatorInterface
{
    private $validators;

    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * Validate customer model
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function validate(CustomerInterface $customer): bool
    {
        foreach ($this->validators as $validator) {
            if (!$validator->validate($customer)) {
                return false;
            }
        }
        return true;
    }

}
