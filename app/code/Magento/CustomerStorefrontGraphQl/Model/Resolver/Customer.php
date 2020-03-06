<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontGraphQl\Model\Resolver;

use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

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
     * Fetch the data from the customer and pass it to graphql according to schema.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        /** @var \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface $extensionAttributes */
        $extensionAttributes = $context->getExtensionAttributes();
        $currentUserId = $context->getUserId();
        if (false === $extensionAttributes->getIsCustomer() || !$currentUserId) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        try {
            $customer = $this->customerRepository->getById($currentUserId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Customer with id "%customer_id" does not exist.', ['customer_id' => $currentUserId]),
                $e
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $this->formatOutput($customer);
    }

    /**
     * Format array for output to graphql
     *
     * TODO this is temp solution
     * Final solution should be more generic and allow for transforming various data object
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function formatOutput(CustomerInterface $customer): array
    {
        $customerArray = $customer->__toArray();
        $customerArray['customer_id'] = $customer->getId();
        $customerArray['date_of_birth'] = $customer->getDateOfBirth();
        $customerArray['id'] = null;
        $customerArray['model'] = $customer;
        return $customerArray;
    }
}
