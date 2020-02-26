<?php
/**
 * Created by PhpStorm.
 * User: pganapat
 * Date: 2/19/20
 * Time: 4:17 PM
 */

namespace Magento\CustomerStorefrontGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerStorefrontServiceApi\Api\CustomerRepositoryInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class UpdateCustomer implements ResolverInterface
{

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerInterfaceFactory,
        TimezoneInterface $timezone
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->timezone = $timezone;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customerData = $args['input'];
        $id = $args['id'];
        $customer = $this->customerInterfaceFactory->create(['data' => $customerData]);
        $createdDate = $this->timezone->date();
        $customer->setId($id);
        $customer->setCreatedIn('service');
        $customer->setStoreId(1);
        $customer->setWebsiteId(1);
        $customer->setCreatedAt((string)$createdDate->getTimestamp());
        $this->customerRepository->save($customer);
        return ['customer' => $customerData];
    }
}