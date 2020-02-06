<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Storage;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;

interface ValidatorInterface
{
    public function validate(CustomerInterface $customer): bool;
}
