<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\ResourceModel;

use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\CustomerStorefrontService\Model\Data\CustomerDocumentFactory as CustomerDocumentFactory;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Validator\Factory as ValidatorFactory;

/**
 * Storefront Customer entity resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer extends AbstractDb
{
    /**
     * CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var ValidatorFactory
     */
    private $_validatorFactory;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var CustomerDocumentFactory
     */
    private $customerDocumentFactory;

    /**
     * Serializable fields
     *
     * @var array
     */
    protected $_serializableFields = ['customer_document' => [[], []]];

    /**
     * Main table name
     *
     * @var string
     */
    protected $_mainTable = 'storefront_customer';

    /**
     * @var string
     */
    protected $_idFieldName = 'customer_id';

    /**
     * @var string
     */
    protected $_tables = ['storefront_customer'];

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
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerDocumentFactory $customerDocumentFactory
     * @param string|null $connectionName
     * @param array $data
     */
    public function __construct(
        DbContext $context,
        ScopeConfigInterface $scopeConfig,
        ValidatorFactory $validatorFactory,
        DateTime $dateTime,
        AccountConfirmation $accountConfirmation,
        CustomerInterfaceFactory $customerFactory,
        CustomerDocumentFactory $customerDocumentFactory,
        string $connectionName = null,
        array $data = []
    ) {
        parent::__construct($context, $connectionName);
        $this->_scopeConfig = $scopeConfig;
        $this->_validatorFactory = $validatorFactory;
        $this->dateTime = $dateTime;
        $this->accountConfirmation = $accountConfirmation;
        $this->customerFactory = $customerFactory;
        $this->customerDocumentFactory = $customerDocumentFactory;
        //$this->setType('customer');
        // Todo: See how can we add separate connections for storefront customer
        //$this->setConnection('customer_read', 'customer_write');
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('storefront_customer', $this->getIdFieldName());
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        //Todo: uniqueness depends on system settings
        $this->_uniqueFields = [
            ['field' => $this->getIdFieldName(), __('Customer ID')],
            ['field' => 'customer_row_id', __('Storefront Customer ID')],
        ];
        return $this;
    }

    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param \Magento\Framework\DataObject|CustomerInterface $customer
     *
     * @return $this
     * @throws AlreadyExistsException
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var CustomerInterface $customer */
        // Todo: Validate store_id as input
        //if ($customer->getStoreId() === null) {
        //    $customer->setStoreId($this->storeManager->getStore()->getId());
        //}

        //Todo: Deal with GroupID
        //$customer->getGroupId();

        parent::_beforeSave($object);

        if (!$customer->getEmail()) {
            throw new ValidatorException(__('The customer email is missing. Enter and try again.'));
        }

        $connection = $this->getConnection();
        $bind = ['email' => $customer->getEmail()];

        $select = $connection->select()->from(
            $this->getTable($this->_mainTable),
            [$this->getIdFieldName()]
        )->where(
            'email = :email'
        );

        //TODO: handle config
        //if ($customer->getSharingConfig()->isWebsiteScope()) {
        $bind['website_id'] = (int)$customer->getWebsiteId();
        $select->where('website_id = :website_id');
        //}
        if ($customer->getId()) {
            $bind[$this->getIdFieldName()] = (int)$customer->getId();
            $select->where("{$this->getIdFieldName()} != :{$this->getIdFieldName()}");
        }

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            throw new AlreadyExistsException(
                __('A customer with the same email address already exists in an associated website.')
            );
        }

        // set confirmation key logic
        if (!$customer->getId() &&
            $this->accountConfirmation->isConfirmationRequired(
                $customer->getWebsiteId(),
                $customer->getId(),
                $customer->getEmail()
            )
        ) {
            $customer->setConfirmation($customer->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$customer->getConfirmation()) {
            $customer->setConfirmation(null);
        }

        if (!$customer->getData('ignore_validation_flag')) {
            $this->_validate($customer);
        }

        return $this;
    }

    /**
     * Validate customer entity
     *
     * @param CustomerInterface $customer
     * @return void
     * @throws ValidatorException
     */
    private function _validate(CustomerInterface $customer)
    {
        $validator = $this->_validatorFactory->createValidator('customer', 'save');

        if (!$validator->isValid($customer)) {
            throw new ValidatorException(
                null,
                null,
                $validator->getMessages()
            );
        }
    }

    /**
     * Save customer addresses and set default addresses in attributes backend
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        // TODO: handle logic about session
//        ObjectManager::getInstance()->get(NotificationStorage::class)->add(
//            NotificationStorage::UPDATE_CUSTOMER_SESSION,
//            $object->getId()
//        );
        return parent::_afterSave($object);
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function loadByField(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $object->beforeLoad($value, $field);
        if ($field === null) {
            $field = $this->getIdFieldName();
        }

        $connection = $this->getConnection();
        if ($connection && $value !== null) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $connection->fetchRow($select);

            if ($data) {
                //$object->setData($key, $value);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);
        $object->afterLoad();
        $object->setOrigData();
        $object->setHasDataChanges(false);

        return $this;
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
        $customer = $this->customerFactory->create(['data' => $object->getData('customer_document')]);
        $object->setData('customer_document', $customer);
    }

    /**
     * Load customer by email
     *
     * @param \Magento\Framework\Model\AbstractModel $customerDocument
     * @param string $email
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail(\Magento\Framework\Model\AbstractModel $customerDocument, $email, $websiteId = null)
    {
        //Todo: getEntityTable comes form EAV.
        $connection = $this->getConnection();
        $bind = ['customer_email' => $email];
        $select = $connection->select()->from(
            $this->getTable($this->_mainTable),
            [$this->getIdFieldName()]
        )->where(
            'email = :customer_email'
        );

        // Todo: figure out how to get Config scope per website or global
//        if ($customer->getSharingConfig()->isWebsiteScope()) {
        if ($websiteId === null) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("A customer website ID wasn't specified. The ID must be specified to use the website scope.")
            );
        }
        $bind['website_id'] = (int)$websiteId;
        $select->where('website_id = :website_id');
//        }

        $customerId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($customerDocument, $customerId);
        } else {
            __("A customer with specified email was not found");
        }

        return $this;
    }

    /**
     * Check whether there are email duplicates of customers in global scope
     *
     * @return bool
     */
    public function findEmailDuplicates()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable($this->_mainTable),
            ['email', 'cnt' => 'COUNT(*)']
        )->group(
            'email'
        )->order(
            'cnt DESC'
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup)) {
            return false;
        }
        return $lookup['cnt'] > 1;
    }

    /**
     * Check customer by id
     *
     * @param int $customerId
     * @return bool
     */
    public function checkCustomerId($customerId)
    {
        $connection = $this->getConnection();
        $bind = [$this->getIdFieldName() => (int)$customerId];
        $select = $connection->select()->from(
            $this->getTable($this->_mainTable),
            [$this->getIdFieldName()]
        )->where(
            "{$this->getIdFieldName()} = :{$this->getIdFieldName()}"
        )->limit(
            1
        );

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Get customer website id
     *
     * @param int $customerId
     * @return int
     */
    public function getWebsiteId($customerId)
    {
        $connection = $this->getConnection();
        $bind = [$this->getIdFieldName() => (int)$customerId];
        $select = $connection->select()->from(
            $this->getTable($this->_mainTable),
            'website_id'
        )->where(
            "{$this->getIdFieldName()} = :{$this->getIdFieldName()}"
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Custom setter of increment ID if its needed
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setNewIncrementId(\Magento\Framework\DataObject $object)
    {
        if ($this->_scopeConfig->getValue(
            \Magento\Customer\Model\Customer::XML_PATH_GENERATE_HUMAN_FRIENDLY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            parent::setNewIncrementId($object);
        }
        return $this;
    }
}
