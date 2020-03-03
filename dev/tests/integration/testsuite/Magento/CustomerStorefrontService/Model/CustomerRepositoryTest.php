<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CustomerRepositoryTest extends TestCase
{
    /** @var CustomerInterfaceFactory */
    private $customerInterfaceFactory;

    /** @var CustomerRepository */
    private $customerRepository;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerInterfaceFactory = $objectManager->get(CustomerInterfaceFactory::class);
        $this->customerRepository = $objectManager->get(CustomerRepository::class);
    }

    public function testSaveNewCustomer()
    {
        $customerData = [
            'website_id' => 1,
            'id' => 99,
            'dob' => '01-01-1985',
            'email' => 'test.customer@example.com',
            'password' => 'password123',
            'group_id' => 1,
            'store_id' => 1,
            'is_active' => 1,
            'prefix' => 'Ms.',
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'taxvat' => '12',
            'gender' => 1
        ];

        $customer = $this->customerInterfaceFactory->create(['data' => $customerData]);

        $savedCustomer = $this->customerRepository->save($customer);
        $this->assertEquals($customerData['email'], $savedCustomer->getEmail());
        $this->assertNotEmpty($savedCustomer->getId());

        $this->customerRepository->delete($savedCustomer);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/customer.php
     */
    public function testSaveUpdateCustomer()
    {
        $customerId = 99;
        $customer = $this->customerRepository->getById($customerId);
        $customer->setDateOfBirth('10-10-1990');
        $customer->setLastname('Doe');

        $this->customerRepository->save($customer);
        $updatedCustomer = $this->customerRepository->getById($customerId);
        $this->assertEquals($customer->getEmail(), $updatedCustomer->getEmail());
        $this->assertEquals('10-10-1990', $updatedCustomer->getDateOfBirth());
        $this->assertEquals('Doe', $updatedCustomer->getLastname());
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/customer.php
     */
    public function testGetById()
    {
        $customerId = 99;
        $customer = $this->customerRepository->getById($customerId);

        $this->assertEquals('test.customer@example.com', $customer->getEmail());
        $this->assertEquals($customerId, $customer->getId());
        $this->assertEquals('Jane', $customer->getFirstname());
        $this->assertEquals('Smith', $customer->getLastname());
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/customer.php
     */
    public function testDelete()
    {
        $customerId = 99;

        $customer = $this->customerRepository->getById($customerId);
        $deleteResult = $this->customerRepository->delete($customer);
        $this->assertTrue($deleteResult);

        $this->expectException(NoSuchEntityException::class);
        $this->customerRepository->getById($customerId);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/customer.php
     */
    public function testDeleteById()
    {
        $customerId = 99;

        $deleteResult = $this->customerRepository->deleteById($customerId);
        $this->assertTrue($deleteResult);

        $this->expectException(NoSuchEntityException::class);
        $this->customerRepository->getById($customerId);
    }
}
