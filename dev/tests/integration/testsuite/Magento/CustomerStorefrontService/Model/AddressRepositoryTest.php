<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddressRepositoryTest extends TestCase
{
    /** @var AddressRepository */
    private $addressRepository;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->addressRepository = $objectManager->get(AddressRepository::class);
        $this->addressFactory = $objectManager->get(AddressInterfaceFactory::class);
    }

    public function testSaveNewAddress()
    {
        $addressData = [
            'id' => 57,
            'country_id' => 'US',
            'street' => ['Green str, 67'],
            'company' => 'CompanyName',
            'telephone' => '5127779999',
            'postcode' => '77777',
            'city' => 'Austin',
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'prefix' => 'Ms.',
            'region' => [
                'region_code' => 'TX',
                'region' => 'Texas',
                'region_id' => 57
            ]
        ];

        $address = $this->addressFactory->create(['data' => $addressData]);
        $this->addressRepository->save($address);
        $savedAddress = $this->addressRepository->getById(57);

        $this->assertEquals($addressData['id'], $savedAddress->getId());
        $this->assertEquals($addressData['street'], $savedAddress->getStreet());
        $this->assertEquals($addressData['postcode'], $savedAddress->getPostcode());

        //TODO region is an array but should be of type RegionInterface
        //$this->assertInstanceOf(RegionInterface::class, $savedAddress->getRegion());
        //$this->assertEquals($addressData['region']['region_code'], $savedAddress->getRegion()->getRegionCode());

        $this->addressRepository->deleteById(57);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/address.php
     */
    public function testGetById()
    {
        $addressId = 57;
        $address = $this->addressRepository->getById($addressId);

        $this->assertEquals($addressId, $address->getId());
        $this->assertEquals('Green str, 67', $address->getStreet()[0]);
        $this->assertEquals('Jane', $address->getFirstname());
        $this->assertEquals('Austin', $address->getCity());
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/customer_with_multiple_addresses.php
     */
    public function testGetList()
    {
        $customerId = 99;
        $addresses = $this->addressRepository->getList($customerId);

        $this->assertCount(2, $addresses);
        $this->assertEquals('Austin', $addresses[0]->getCity());
        $this->assertTrue($addresses[0]->isDefaultShipping());
        $this->assertEquals('Toronto', $addresses[1]->getCity());
        $this->assertTrue($addresses[1]->isDefaultBilling());
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontService/_files/customer_with_multiple_addresses.php
     */
    public function testDelete()
    {
        $customerId = 99;
        $addresses = $this->addressRepository->getList($customerId);

        $this->assertCount(2, $addresses);
        $addressIds = array_map(function ($address) {
            return $address->getId();
        }, $addresses);

        $deleteResult = $this->addressRepository->delete($addresses[0]);
        $this->assertTrue($deleteResult);

        $addressesAfterDelete = $this->addressRepository->getList($customerId);
        $this->assertCount(1, $addressesAfterDelete);
        $this->assertEquals($addressIds[1], $addressesAfterDelete[0]->getId());
    }
}
