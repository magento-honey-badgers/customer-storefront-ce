<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontGraphQl\Model\Resolver;

use Magento\CustomerStorefrontApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontApi\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

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
        /** @var \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface $extensionAttributes */
        $extensionAttributes =  $context->getExtensionAttributes();
        $currentUserId = $context->getUserId();
        /** @var ContextInterface $context */
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
            // @codeCoverageIgnoreEnd
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
        return $customerArray;
    }
}
