<?php
/**
 * Customer address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\ResourceModel;

use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address\DeleteRelation;
use Magento\CustomerStorefrontService\Model\Data\AddressDocument as AddressDocumentDto;
use Magento\CustomerStorefrontService\Model\Data\CustomerDocumentFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterfaceFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Validator\Factory as ValidatorFactory;

/**
 * Class AddressDocument
 */
class AddressDocument extends AbstractDb
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    private $_validatorFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var DeleteRelation
     */
    private $deleteRelation;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * Serializable fields
     *
     * @var array
     */
    protected $_serializableFields = ['customer_address_document' => [[], []]];

    /**
     * Main table name
     *
     * @var string
     */
    protected $_mainTable = 'storefront_customer_address';

    /**
     * @var string
     */
    protected $_idFieldName = 'customer_address_row_id';

    /**
     * @var string
     */
    protected $_tables = ['storefront_customer_address'];

    /**
     * @var array
     */
    protected $_connections = ['customer_read', 'customer_write'];

    /**
     * @param DbContext $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ValidatorFactory $validatorFactory
     * @param DateTime $dateTime,
     * @param AccountConfirmation $accountConfirmation
     * @param AddressInterfaceFactory $addressFactory
     * @param CustomerDocumentFactory $customerDocumentFactory
     * @param DeleteRelation $deleteRelation
     * @param CustomerRegistry $customerRegistry
     * @param string|null $connectionName
     * @param array $data
     */
    public function __construct(
        DbContext $context,
        ScopeConfigInterface $scopeConfig,
        ValidatorFactory $validatorFactory,
        DateTime $dateTime,
        AccountConfirmation $accountConfirmation,
        AddressInterfaceFactory $addressFactory,
        CustomerDocumentFactory $customerDocumentFactory,
        DeleteRelation $deleteRelation,
        CustomerRegistry $customerRegistry,
        string $connectionName = null,
        array $data = []
    ) {
        parent::__construct($context, $connectionName);
        $this->_scopeConfig = $scopeConfig;
        $this->_validatorFactory = $validatorFactory;
        $this->dateTime = $dateTime;
        $this->accountConfirmation = $accountConfirmation;
        $this->addressFactory = $addressFactory;
        $this->customerDocumentFactory = $customerDocumentFactory;
        $this->customerRegistry = $customerRegistry;
        $this->deleteRelation = $deleteRelation;
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('storefront_customer_address', 'customer_address_row_id');
        $this->connectionName = 'customer';
    }

    /**
     * Check customer address before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $address
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $address)
    {
        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate customer address entity
     *
     * @param \Magento\Framework\DataObject $address
     * @return void
     * @throws \Magento\Framework\Validator\Exception When validation failed
     */
    private function _validate($address)
    {
        if ($address->getDataByKey('should_ignore_validation')) {
            return;
        }
        $validator = $this->_validatorFactory->createValidator('customer_address', 'save');

        if (!$validator->isValid($address)) {
            throw new \Magento\Framework\Validator\Exception(
                null,
                null,
                $validator->getMessages()
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        $result = parent::delete($object);
        $object->setData([]);
        return $result;
    }

    /**
     * After delete entity process
     *
     * @param AddressDocumentDto $address
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $address)
    {
        $customer = $this->customerRegistry->retrieve($address->getCustomerId());

        $this->deleteRelation->deleteRelation($address, $customer);
        return parent::_afterDelete($address);
    }

    /**
     * Replace 'customer_document' with DTO from Array
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $field
     * @param mixed $defaultValue
     * @return void
     */
    protected function _unserializeField(DataObject $object, $field, $defaultValue = null)
    {
        parent::_unserializeField($object, $field, $defaultValue);
        $customer = $this->addressFactory->create(['data' => $object->getData('customer_document')]);
        $object->setData('customer_document', $customer);
    }
}
