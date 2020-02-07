<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\ResourceModel\Address;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\CustomerStorefrontService\Model\Data\AddressDocument;
use Magento\CustomerStorefrontService\Model\ResourceModel\AddressDocument as AddressDocumentResource;

/**
 * Customer Addresses collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var int
     */
    private $customerId = null;

    /**
     * @var array
     */
    private $selectFieldsNames = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param array $selectFieldsNames
     * @param int|null $customerId
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        array $selectFieldsNames = [],
        int $customerId = null
    ) {
        $this->customerId = $customerId;
        $this->selectFieldsNames = $selectFieldsNames;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            AddressDocument::class,
            AddressDocumentResource::class
        );

        if ($this->customerId !== null) {
            $this->addFilter('customer_id', $this->customerId);
        } else {
            $this->addFilter('customer_id', -1);
        }

        if (!empty($this->selectFieldsNames)) {
            foreach ($this->selectFieldsNames as $fieldName) {
                $this->addFieldToSelect($fieldName);
            }
        }
    }
}
