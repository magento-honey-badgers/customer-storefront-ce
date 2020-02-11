<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Temporary solution to add generated columns to schema
 *
 * TODO: MC-31333
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        //Setup Customer table
        $customerTable = $setup->getTable('storefront_customer');
        $this->setupCustomerTable($setup, $customerTable);
        //Setup Address table
        $addressTable = $setup->getTable('storefront_customer_address');
        $this->setupAddressTable($setup, $addressTable);

        $setup->endSetup();
    }

    /**
     * Configure storefront_customer table
     *
     * @param SchemaSetupInterface $setup
     * @param string $customerTable
     */
    private function setupCustomerTable(SchemaSetupInterface $setup, string $customerTable)
    {
        $addColumnSql = <<<ADDCOLUMN
ALTER TABLE `$customerTable`
    ADD COLUMN `email` varchar(255)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_document`,'$.email'))) STORED,
    ADD COLUMN `website_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_document`,'$.website_id'))) STORED,
    ADD COLUMN `store_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_document`,'$.store_id'))) STORED,
    ADD COLUMN `customer_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_document`,'$.id'))) STORED,
    ADD COLUMN `default_billing_address_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_document`,'$.default_billing'))) STORED NULL,
    ADD COLUMN `default_shipping_address_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_document`,'$.default_shipping'))) STORED NULL;
ADDCOLUMN;

        //Add Columns
        $setup->getConnection()->query($addColumnSql);

        //Add Unique Index
        $setup->getConnection()->addIndex(
            $customerTable,
            'STOREFRONT_CUSTOMER_EMAIL_WEBSITE_ID',
            ['email', 'website_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
        //Add Indexes
        $indexes = [
            'STOREFRONT_CUSTOMER_EMAIL' => 'email',
            'STOREFRONT_CUSTOMER_CUSTOMER_ID' => 'customer_id',
            'STOREFRONT_CUSTOMER_WEBSITE_ID' => 'website_id',
            'STOREFRONT_CUSTOMER_STORE_ID' => 'store_id',
            'STOREFRONT_CUSTOMER_DEFAULT_BILLING_ADDRESS_ID' => 'default_billing_address_id',
            'STOREFRONT_CUSTOMER_DEFAULT_SHIPPING_ADDRESS_ID' => 'default_shipping_address_id'
        ];
        foreach ($indexes as $indexName => $column) {
            $setup->getConnection()->addIndex(
                $customerTable,
                $indexName,
                $column,
                AdapterInterface::INDEX_TYPE_INDEX
            );
        }
    }

    /**
     * Configure storefront_customer_address table
     *
     * @param SchemaSetupInterface $setup
     * @param string $addressTable
     */
    private function setupAddressTable(SchemaSetupInterface $setup, string $addressTable)
    {
        $addColumnSql = <<<ADDCOLUMN
ALTER TABLE `$addressTable`
   ADD COLUMN `customer_row_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_address_document`,'$.customer_row_id'))) STORED,
   ADD COLUMN `customer_address_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_address_document`,'$.id'))) STORED,
   ADD COLUMN `customer_id` int(11)
        GENERATED ALWAYS AS (json_unquote(json_extract(`customer_address_document`,'$.customer_id'))) STORED;
ADDCOLUMN;

        //Add Columns
        $setup->getConnection()->query($addColumnSql);

        //Add Unique Index
        $setup->getConnection()->addIndex(
            $addressTable,
            'STOREFRONT_CUSTOMER_ADDRESS_ADDRESS_ID',
            ['customer_address_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
        //Add Indexes
        $indexes = [
            'STOREFRONT_CUSTOMER_ADDRESS_ROW_ID' => 'customer_row_id',
            'STOREFRONT_CUSTOMER_ADDRESS_CUSTOMER_ID' => 'customer_id'
        ];
        foreach ($indexes as $indexName => $column) {
            $setup->getConnection()->addIndex(
                $addressTable,
                $indexName,
                $column,
                AdapterInterface::INDEX_TYPE_INDEX
            );
        }
    }
}
