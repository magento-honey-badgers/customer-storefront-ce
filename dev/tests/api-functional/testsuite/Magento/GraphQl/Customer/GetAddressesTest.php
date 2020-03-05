<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for customer address retrieval.
 */
class GetAddressesTest extends GraphQlAbstract
{
    /** @var CustomerTokenServiceInterface */
    private $customerTokenService;

    /** @var PublisherConsumerController */
    private $publisherConsumerController;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();

        $this->publisherConsumerController = $objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => [
                        'customer.monolith.messageBroker.customer.save',
                        'customer.monolith.messageBroker.address.save',
                        'customer.messageBroker.service.customer.save',
                        'customer.messageBroker.service.address.save',
                        'customer.monolith.messageBroker.customer.delete',
                        'customer.messageBroker.service.customer.delete',
                        'customer.messageBroker.service.address.delete',
                        'customer.monolith.messageBroker.address.delete'
                    ],
                'logFilePath' => TESTS_TEMP_DIR . "/CustomerStorefrontMessageQueueTestLog.txt",
                'maxMessages' => 500,
                'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
            ]
        );

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail($e->getMessage());
        }

        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    protected function tearDown()
    {
        $this->publisherConsumerController->stopConsumers();
    }
    /**
     * @magentoApiDataFixture Magento/CustomerMessageBroker/_files/customer_with_address.php
     */
    public function testGetCustomerWithAddresses()
    {
        $query = $this->getQuery();

        $userName = 'customer@example.com';
        $password = 'password';

        $customerToken = $this->customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');

        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('addresses', $response['customer']);
        $this->assertTrue(
            is_array([$response['customer']['addresses']]),
            " Addresses field must be of an array type."
        );
        $this->assertNotEmpty($response['customer']['addresses']);
        $this->assertEquals('US', $response['customer']['addresses'][0]['country_id']);
        $this->assertEquals('5127779999', $response['customer']['addresses'][0]['telephone']);
        $this->assertCustomerAddressesFields($customer, $response);
    }

    /**
     * Verify the fields for CustomerAddress object
     *
     * @param CustomerInterface $customer
     * @param array $actualResponse
     */
    private function assertCustomerAddressesFields($customer, $actualResponse)
    {
        /** @var AddressInterface $addresses */
        $addresses = $customer->getAddresses();
        foreach ($addresses as $addressKey => $addressValue) {
            $this->assertNotEmpty($addressValue);
            $assertionMap = [
                ['response_field' => 'id', 'expected_value' => $addresses[$addressKey]->getId()],
                ['response_field' => 'country_id', 'expected_value' => $addresses[$addressKey]->getCountryId()],
                ['response_field' => 'telephone', 'expected_value' => $addresses[$addressKey]->getTelephone()],
                ['response_field' => 'postcode', 'expected_value' => $addresses[$addressKey]->getPostcode()],
                ['response_field' => 'city', 'expected_value' => $addresses[$addressKey]->getCity()],
                ['response_field' => 'firstname', 'expected_value' => $addresses[$addressKey]->getFirstname()],
                ['response_field' => 'lastname', 'expected_value' => $addresses[$addressKey]->getLastname()]
            ];
            $this->assertResponseFields($actualResponse['customer']['addresses'][$addressKey], $assertionMap);
        }
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
{
  customer {
    id
    addresses {
      id
      customer_id
      region_id
      country_id
      telephone
      postcode
      city
      firstname
      lastname
    }
   }
}
QUERY;
    }
}
