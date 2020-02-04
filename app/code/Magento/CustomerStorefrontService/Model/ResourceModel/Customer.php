<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontService\Model\ResourceModel;

use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
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
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * Serializable field: additional_information
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
     * @param array $data
     */
    public function __construct(
        DbContext $context,
        ScopeConfigInterface $scopeConfig,
        ValidatorFactory $validatorFactory,
        DateTime $dateTime,
        AccountConfirmation $accountConfirmation,
        string $connectionName = null,
        array $data = []
    ) {
        parent::__construct($context, $connectionName);
        $this->_scopeConfig = $scopeConfig;
        $this->_validatorFactory = $validatorFactory;
        $this->dateTime = $dateTime;
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
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
        $this->_init('storefront_customer', 'customer_id');
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
            ['field' => 'customer_id', __('Customer ID')],
            ['field' => 'storefront_customer_id', __('Storefront Customer ID')],
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
            [$this->_idFieldName]
        )->where(
            'email = :email'
        );

        //TODO: handle config
        //if ($customer->getSharingConfig()->isWebsiteScope()) {
        $bind['website_id'] = (int)$customer->getWebsiteId();
        $select->where('website_id = :website_id');
        //}
        if ($customer->getId()) {
            $bind['entity_id'] = (int)$customer->getId();
            $select->where('entity_id != :entity_id');
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
    protected function _validate(CustomerInterface $customer)
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
     * Retrieve notification storage
     *
     * @return NotificationStorage
     */
    private function getNotificationStorage()
    {
        if ($this->notificationStorage === null) {
            $this->notificationStorage = ObjectManager::getInstance()->get(NotificationStorage::class);
        }
        return $this->notificationStorage;
    }

    /**
     * Save customer addresses and set default addresses in attributes backend
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->getNotificationStorage()->add(
            NotificationStorage::UPDATE_CUSTOMER_SESSION,
            $object->getId()
        );
        return parent::_afterSave($object);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param \Magento\Framework\DataObject $object
     * @param string|int $rowId
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = parent::_getLoadRowSelect($object, $rowId);
        if ($object->getWebsiteId() && $object->getSharingConfig()->isWebsiteScope()) {
            $select->where('website_id =?', (int)$object->getWebsiteId());
        }

        return $select;
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Api\AbstractSimpleObject $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function loadByField(\Magento\Framework\Api\AbstractSimpleObject $object, $value, $field = null)
    {
        //$object->beforeLoad($value, $field);
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
        //$this->_afterLoad($object);
        //$object->afterLoad();
        //$object->setOrigData();
        //$object->setHasDataChanges(false);

        return $this;
    }

    /**
     * Load customer by email
     *
     * @param CustomerInterface $customer
     * @param string $email
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail(CustomerInterface $customer, $email)
    {
        //Todo: getEntityTable comes form EAV.
        $connection = $this->getConnection();
        $bind = ['customer_email' => $email];
        $select = $connection->select()->from(
            $this->getTable($this->_mainTable),
            [$this->_idFieldName]
        )->where(
            'email = :customer_email'
        );

        // Todo: figure out how to get Config
//        if ($customer->getSharingConfig()->isWebsiteScope()) {
//            if (!$customer->hasData('website_id')) {
//                throw new \Magento\Framework\Exception\LocalizedException(
//                    __("A customer website ID wasn't specified. The ID must be specified to use the website scope.")
//                );
//            }
//            $bind['website_id'] = (int)$customer->getWebsiteId();
//            $select->where('website_id = :website_id');
//        }
        if (!$customer->getWebsiteId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("A customer website ID wasn't specified. The ID must be specified to use the website scope.")
            );
        }
        $bind['website_id'] = (int)$customer->getWebsiteId();
        $select->where('website_id = :website_id');

        $customerId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($customer, $customerId);
        } else {
            $customer->setData([]);
        }

        return $this;
    }

    /**
     * Change customer password
     *
     * @param CustomerInterface $customer
     * @param string $newPassword
     * @return $this
     */
    public function changePassword(CustomerInterface $customer, $newPassword)
    {
        $customer->setPassword($newPassword);
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
            $this->getTable('customer_entity'),
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
        $bind = ['entity_id' => (int)$customerId];
        $select = $connection->select()->from(
            $this->getTable('customer_entity'),
            'entity_id'
        )->where(
            'entity_id = :entity_id'
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
        $bind = ['entity_id' => (int)$customerId];
        $select = $connection->select()->from(
            $this->getTable('customer_entity'),
            'website_id'
        )->where(
            'entity_id = :entity_id'
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

    /**
     * Change reset password link token
     *
     * Stores new reset password link token and its creation time
     *
     * @param CustomerInterface $customer
     * @param string $passwordLinkToken
     * @return $this
     */
    public function changeResetPasswordLinkToken(CustomerInterface $customer, $passwordLinkToken)
    {
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $customer->setRpToken($passwordLinkToken);
            $customer->setRpTokenCreatedAt(
                (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            );
        }
        return $this;
    }
}
