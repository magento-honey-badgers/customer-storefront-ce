<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Queue\Consumer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test address save consumer
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    private $addressConsumer;

    /**
     * @var PublisherInterface|MockObject
     */
    private $publisherMock;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->addressRepository = $objectManager->get(AddressRepositoryInterface::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->publisherMock = $this->createMock(PublisherInterface::class);
        $this->addressConsumer = $objectManager->create(
            Address::class,
            ['publisher' => $this->publisherMock]
        );
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     */
    public function testForwardAddressChanges()
    {
        $address = $this->getAddressByField('postcode', '77777');
        $mockMessage = $this->serializer->serialize([
            'correlation_id' => '1000',
            'data' => ['id' => $address->getId()]
        ]);

        $this->publisherMock
            ->expects($this->once())
            ->method('publish')
            ->with(
                Address::SAVE_TOPIC,
                $this->logicalAnd(
                    $this->arrayHasKey('data'),
                    $this->arrayHasKey('entity_type'),
                    $this->arrayHasKey('action')
                )
            );

        $this->addressConsumer->forwardAddressChanges($mockMessage);
    }

    /**
     * @magentoDataFixture Magento/CustomerStorefrontConnector/_files/customer_with_address.php
     */
    public function testForwardAddressDelete()
    {
        $address = $this->getAddressByField('postcode', '77777');
        $mockMessage = $this->serializer->serialize([
            'correlation_id' => '1000',
            'data' => ['id' => $address->getId()]
        ]);

        $this->publisherMock
            ->expects($this->once())
            ->method('publish')
            ->with(
                Address::DELETE_TOPIC,
                $this->logicalAnd(
                    $this->arrayHasKey('data'),
                    $this->arrayHasKey('entity_type'),
                    $this->arrayHasKey('action')
                )
            );

        $this->addressConsumer->forwardAddressDelete($mockMessage);
    }

    /**
     * Fetch address by field value
     *
     * @param string $field
     * @param string $value
     * @return AddressInterface
     */
    private function getAddressByField(string $field, string $value)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter($field, $value)->create();
        $addressResults = $this->addressRepository->getList($searchCriteria);

        return $addressResults->getItems()[0];
    }
}
