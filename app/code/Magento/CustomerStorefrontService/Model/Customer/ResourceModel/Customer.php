<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontService\Model\ResourceModel;

use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Validator\Exception as ValidatorException;

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
    protected $_serializableFields = ['customer_document' => [null, []]];

    /**
     * Main table name
     *
     * @var string
     */
    protected $_mainTable = 'storefront_customer';

    /**
     * @var string
     */
    protected $_idFieldName = 'storefront_customer_id';

    /**
     * @var string
     */
    protected $_tables = ['storefront_customer'];

    /**
     * @var array
     */
    protected $_connections = ['customer_read', 'customer_write'];

    /**
     * @var string
     */
    protected $connectionName = 'customer_read';

    /**
     * Customer constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime,
     * @param AccountConfirmation $accountConfirmation
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        AccountConfirmation $accountConfirmation,
        $data = []
    ) {
        parent::__construct($context);
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
        $this->_init('storefront_customer', 'storefront_customer_id');
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
            ['field' => 'email', __('Email')],
            ['field' => 'website_id', 'title' => __('Website')]
        ];
        return $this;
    }

    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param \Magento\Framework\DataObject|\Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return $this
     * @throws AlreadyExistsException
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $customer)
    {
        /** @var CustomerInterface $customer */
        // Todo: Validate store_id as input
        //if ($customer->getStoreId() === null) {
        //    $customer->setStoreId($this->storeManager->getStore()->getId());
        //}
        $customer->getGroupId();

        parent::_beforeSave($customer);

        if (!$customer->getEmail()) {
            throw new ValidatorException(__('The customer email is missing. Enter and try again.'));
        }

        $connection = $this->getConnection();
        $bind = ['email' => $customer->getEmail()];

        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :email'
        );
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }
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
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     * @throws ValidatorException
     */
    protected function _validate($customer)
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
     * @param \Magento\Framework\DataObject $customer
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $customer)
    {
        $this->getNotificationStorage()->add(
            NotificationStorage::UPDATE_CUSTOMER_SESSION,
            $customer->getId()
        );
        return parent::_afterSave($customer);
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
     * Load customer by email
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $email
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail(\Magento\Customer\Model\Customer $customer, $email)
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

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            if (!$customer->hasData('website_id')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("A customer website ID wasn't specified. The ID must be specified to use the website scope.")
                );
            }
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }

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
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $newPassword
     * @return $this
     */
    public function changePassword(\Magento\Customer\Model\Customer $customer, $newPassword)
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
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $passwordLinkToken
     * @return $this
     */
    public function changeResetPasswordLinkToken(\Magento\Customer\Model\Customer $customer, $passwordLinkToken)
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
