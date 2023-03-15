<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Setup\Table;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

abstract class AbstractTable
{
    const TABLE_NAME = '';

    /**
     * Magento table names
     * table_[name]
     */
    const TABLE_STORE = 'store';
    const TABLE_CUSTOMER = 'customer_entity';
    const TABLE_USER = 'admin_user';

    /**
     * Magento column names
     * column_[table]_[name]
     */
    const COLUMN_STORE_ID = 'store_id';
    const COLUMN_CUSTOMER_ID = 'entity_id';
    const COLUMN_USER_ID = 'user_id';

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    public function createTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->getTableConfig($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    abstract public function getTableConfig(SchemaSetupInterface $setup): Table;
}
