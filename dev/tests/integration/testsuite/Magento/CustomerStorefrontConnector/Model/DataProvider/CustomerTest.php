<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Model\DataProvider;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Customer data provider
 */
class CustomerTest extends TestCase
{
    /**
     * @var Customer
     */
    private $customerDataProvider;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerDataProvider = $objectManager->get(Customer::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     * @dataProvider customerDataProvider
     * @param array $expectedData
     */
    public function testGetData($expectedData)
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $customerData = $this->customerDataProvider->getData((int)$customer->getId());

        $this->assertEquals($customer->getId(), $customerData['id']);
        foreach ($expectedData as $key => $expected) {
            $this->assertEquals($expected, $customerData[$key]);
        }
        $this->assertArrayHasKey('addresses', $customerData);
        $this->assertCount(1, $customerData['addresses']);
        $this->assertArrayHasKey('extension_attributes', $customerData);
        $this->assertTrue(is_array($customerData['extension_attributes']));
    }

    /**
     * Expected customer data
     *
     * @return array
     */
    public function customerDataProvider(): array
    {
        return [
            [
                [
                    'dob' => '1970-01-01',
                    'email' => 'customer@example.com',
                    'prefix' => 'Mr.',
                    'firstname' => 'John',
                    'lastname' => 'Smith',
                    'middlename' => 'A',
                    'gender' => '0',
                    'suffix' => 'Esq.',
                    'taxvat' => '12'
                ]
            ]
        ];
    }
}
