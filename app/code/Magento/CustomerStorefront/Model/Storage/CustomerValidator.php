<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefront\Model\Storage;

use Magento\CustomerStorefrontApi\Api\Data\CustomerInterface;
use function implode;

/**
 * Composite validator for customer
 */
class CustomerValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param array $validators
     */
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

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): ?string
    {
        $errorMessages = [];
        foreach ($this->validators as $validator) {
            if (!empty($validator->getErrorMessage())) {
                $errorMessages[] = $validator->getErrorMessage();
            }
        }

        return empty($errorMessages) ? null : implode("\n", $errorMessages);
    }
}
