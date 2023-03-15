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

use MageMe\WebForms\Api\Data\StoreInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class StoreTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_store';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(StoreInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(StoreInterface::STORE_ID, Table::TYPE_SMALLINT, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Store ID')
            ->addColumn(StoreInterface::ENTITY_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Entity ID')
            ->addColumn(StoreInterface::ENTITY_TYPE, Table::TYPE_TEXT, 10, [
            ], 'Entity Type')
            ->addColumn(StoreInterface::STORE_DATA_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Data in JSON')
            ->setComment(
                'WebForms Store Data'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, StoreInterface::STORE_ID,
                    self::TABLE_STORE, self::COLUMN_STORE_ID),
                StoreInterface::STORE_ID,
                $setup->getTable(self::TABLE_STORE),
                self::COLUMN_STORE_ID,
                Table::ACTION_CASCADE
            )
            ->addIndex(
                $setup->getIdxName(
                    self::TABLE_NAME,
                    [
                        StoreInterface::STORE_ID,
                        StoreInterface::ENTITY_ID,
                        StoreInterface::ENTITY_TYPE
                    ],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                    StoreInterface::STORE_ID,
                    StoreInterface::ENTITY_ID,
                    StoreInterface::ENTITY_TYPE
                ],
                [
                    'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );
    }
}
