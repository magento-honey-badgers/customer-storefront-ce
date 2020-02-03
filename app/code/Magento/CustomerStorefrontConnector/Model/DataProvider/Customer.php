<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer data provider
 */
class Customer
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerTransformer
     */
    private $customerTransformer;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerTransformer $customerTransformer
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerTransformer $customerTransformer,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerTransformer = $customerTransformer;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Fetch Customer data
     *
     * @param int $id
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData(int $id): array
    {
        // Avoid using getByID() to avoid stale data in CustomerRegistry
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $id)->create();
        $customerResults = $this->customerRepository->getList($searchCriteria);
        $customerResultItems = $customerResults->getItems();
        if (empty($customerResultItems)) {
            throw NoSuchEntityException::singleField('customerId', $id);
        }

        $customer = $customerResultItems[0];

        return $this->customerTransformer->toArray($customer);
    }
}
