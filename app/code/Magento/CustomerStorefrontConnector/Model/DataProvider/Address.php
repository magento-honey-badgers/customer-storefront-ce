<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer Address data provider
 */
class Address
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressTransformer
     */
    private $addressTransformer;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressTransformer $addressTransformer
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressTransformer $addressTransformer,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressTransformer = $addressTransformer;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Fetch Address data
     *
     * @param int $addressId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getData(int $addressId): array
    {
        // Avoid using getByID() to avoid stale data in AddressRegistry
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $addressId)->create();
        $addressResults = $this->addressRepository->getList($searchCriteria);
        $addressResultItems = $addressResults->getItems();
        if (empty($addressResultItems)) {
            throw NoSuchEntityException::singleField('addressId', $addressId);
        }

        return $this->addressTransformer->toArray($addressResultItems[0]);
    }
}
