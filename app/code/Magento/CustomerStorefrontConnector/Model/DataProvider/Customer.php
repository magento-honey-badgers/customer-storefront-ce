<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\CustomerRepositoryInterface;
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
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerTransformer $customerTransformer
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerTransformer $customerTransformer
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerTransformer = $customerTransformer;
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
        $customer = $this->customerRepository->getById($id);

        return $this->customerTransformer->toArray($customer);
    }
}
