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
 * Test Address data provider
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    private $addressDataProvider;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->addressDataProvider = $objectManager->get(Address::class);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     * @dataProvider addressDataProvider
     * @param array $expectedData
     */
    public function testGetData(array $expectedData)
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $addressData = $this->addressDataProvider->getData((int) $customer->getDefaultShipping());

        $this->assertEquals($customer->getId(), $addressData['customer_id']);
        foreach ($expectedData as $key => $expected) {
            $this->assertEquals($expected, $addressData[$key]);
        }
        $this->assertArrayHasKey('extension_attributes', $addressData);
        $this->assertTrue(is_array($addressData['extension_attributes']));
    }

    /**
     * Expected address data
     *
     * @return array
     */
    public function addressDataProvider(): array
    {
        return
        [
            [
                [
                    'telephone' => '5127779999',
                    'postcode' => '77777',
                    'country_id' => 'US',
                    'city' => 'CityM',
                    'company' => 'CompanyName',
                    'street' => ['Green str, 67'],
                    'lastname' => 'Smith',
                    'firstname' => 'John',
                    'region' => [
                        'region' => 'Alabama',
                        'region_id' => 1,
                        'region_code' => 'AL'
                    ]
                ]
            ]
        ];
    }
}
