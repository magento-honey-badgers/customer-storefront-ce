<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontGraphQl\Model\Resolver;

use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Customers field resolver
 */
class Customer implements ResolverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $currentUserId = $context->getUserId();
            if ($currentUserId) {
                /** @var \Magento\CustomerStorefrontService\Model\Data\Customer $customer */
                $customer = $this->customerRepository->getById($currentUserId);
            } else {
                throw new GraphQlNoSuchEntityException(__('Customer not authenticated'));
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
        $customerArray = $customer->__toArray();
        $customerArray['customer_id'] = $currentUserId;
        return $customerArray;
    }
}
