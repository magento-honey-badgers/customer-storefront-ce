<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontConnector\Model\AddressRepositoryWrapper;
use Magento\CustomerStorefrontConnector\Queue\Consumer\Address as AddressConsumer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\QueueMessageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test combined functioning of synchronizer and connector
 */
class AddressTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var AddressConsumer */
    private $addressConsumer;

    /** @var AddressRepositoryWrapper|MockObject  */
    private $addressRepositoryWrapperMock;

    /** @var QueueMessageHelper */
    private $messageHelper;

    private static $queues = [
        'monolithCustomerSave' => 'customer.monolith.connector.customer.save',
        'monolithAddressSave' => 'customer.monolith.connector.address.save',
        'monolithCustomerDelete' => 'customer.monolith.connector.customer.delete',
        'monolithAddressDelete' => 'customer.monolith.connector.address.delete',
        'serviceCustomerSave' => 'customer.connector.service.customer.save',
        'serviceAddressSave' => 'customer.connector.service.address.save',
        'serviceCustomerDelete' => 'customer.connector.service.customer.delete',
        'serviceAddressDelete' => 'customer.connector.service.address.delete'
    ];

    protected function setup()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->messageHelper = $this->objectManager->get(QueueMessageHelper::class);
        //AddressRepositoryWrapper must be mocked because REST calls are not possible in integration test env
        $this->addressRepositoryWrapperMock = $this->createMock(AddressRepositoryWrapper::class);

        $this->addressConsumer = $this->objectManager->create(
            AddressConsumer::class,
            ['addressRepository' => $this->addressRepositoryWrapperMock]
        );
    }

    /**
     * Clean up queues before test starts
     */
    public static function setUpBeforeClass()
    {
        $messageHelper = Bootstrap::getObjectManager()->get(QueueMessageHelper::class);
        $messageHelper->acknowledgeAllMessages(self::$queues);
    }

    /**
     * Clean up queues between tests
     */
    protected function tearDown()
    {
        $this->messageHelper->acknowledgeAllMessages(self::$queues);
    }

    /**
     * Clean up queues of rollback messages
     */
    public static function tearDownAfterClass()
    {
        $messageHelper = Bootstrap::getObjectManager()->get(QueueMessageHelper::class);
        $messageHelper->acknowledgeAllMessages(self::$queues);
    }

    /**
     * Test forward customer address change events
     *
     * @param array $addressData
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     * @dataProvider addressDataProvider
     */
    public function testForwardCustomerAddressChanges(array $addressData)
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        $customerAddress = $this->addressRepository->getById($customer->getDefaultBilling());
        $addressId = $customerAddress->getId();

        $this->addressRepositoryWrapperMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressData);

        $monolithAddressSaveMessage = $this->messageHelper->popMessage(self::$queues['monolithAddressSave']);
        $this->addressConsumer->forwardAddressChanges($monolithAddressSaveMessage);
        $serviceAddressSaveMessage = $this->messageHelper->popMessage(self::$queues['serviceAddressSave']);

        $monolithAddressSaveData = $this->serializer->unserialize($monolithAddressSaveMessage);
        $serviceAddressSaveData = $this->serializer->unserialize($serviceAddressSaveMessage);

        $this->assertNotEmpty($serviceAddressSaveData);
        $this->assertArrayHasKey('correlation_id', $serviceAddressSaveData);
        $this->assertEquals($monolithAddressSaveData['correlation_id'], $serviceAddressSaveData['correlation_id']);
        $this->assertEquals('address', $serviceAddressSaveData['entity_type']);
        $this->assertEquals($monolithAddressSaveData['entity_type'], $serviceAddressSaveData['entity_type']);
        $this->assertEquals('update', $serviceAddressSaveData['event']);
        $this->assertEquals($monolithAddressSaveData['event'], $serviceAddressSaveData['event']);
        $this->assertNotEmpty($serviceAddressSaveData['data']);
        $this->assertEquals($addressData['street'], $serviceAddressSaveData['data']['street']);
        $this->assertEquals($addressData['firstname'], $serviceAddressSaveData['data']['firstname']);
        $this->assertEquals($addressData['city'], $serviceAddressSaveData['data']['city']);
        $this->assertTrue($serviceAddressSaveData['data']['default_shipping']);
    }

    /**
     * Test forward address delete message along queues
     *
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     */
    public function testForwardCustomerAddressDelete()
    {
        $customer = $this->customerRepository->get('customer@example.com', 1);
        //Do delete address
        $this->addressRepository->deleteById($customer->getDefaultShipping());

        $monolithAddressDeleteMessage = $this->messageHelper->popMessage(self::$queues['monolithAddressDelete']);
        $this->addressConsumer->forwardAddressDelete($monolithAddressDeleteMessage);
        $serviceAddressDeleteMessage = $this->messageHelper->popMessage(self::$queues['serviceAddressDelete']);

        $monolithAddressDeleteData = $this->serializer->unserialize($monolithAddressDeleteMessage);
        $serviceAddressDeleteData = $this->serializer->unserialize($serviceAddressDeleteMessage);

        $this->assertArrayHasKey('correlation_id', $serviceAddressDeleteData);
        $this->assertEquals($monolithAddressDeleteData['correlation_id'], $serviceAddressDeleteData['correlation_id']);
        $this->assertEquals('address', $serviceAddressDeleteData['entity_type']);
        $this->assertEquals($monolithAddressDeleteData['entity_type'], $serviceAddressDeleteData['entity_type']);
        $this->assertEquals('delete', $serviceAddressDeleteData['event']);
        $this->assertEquals($monolithAddressDeleteData['event'], $serviceAddressDeleteData['event']);
        $this->assertEquals($monolithAddressDeleteData['data']['id'], $serviceAddressDeleteData['data']['id']);
    }

    /**
     * Address data to mock REST response
     *
     * @return array
     */
    public function addressDataProvider()
    {
        return [
            [
                [
                    'country_id' => 'US',
                    'street' => ['Green str, 67'],
                    'company' => 'CompanyName',
                    'telephone' => '5127779999',
                    'postcode' => '77777',
                    'city' => 'CityM',
                    'firstname' => 'Johny',
                    'lastname' => 'Smith',
                    'middlename' => 'A',
                    'prefix' => 'Mr.',
                    'suffix' => 'Esq',
                    'default_shipping' => true,
                    'default_billing' => true,
                    'region' => [
                        'region_code' => 'AL',
                        'region' => 'Alabama',
                        'region_id' => 1
                    ],
                    'extension_attributes' => []
                ]
            ]
        ];
    }
}
