<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Storage;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

/**
 * Validator
 */
interface ValidatorInterface
{
    /**
     * Perform validation
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function validate(CustomerInterface $customer): bool;

    /**
     * Return validation error message
     *
     * @return string
     */
    public function getErrorMessage(): ?string;
}
