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

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class LogicTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_logic';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(LogicInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(LogicInterface::FIELD_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Field ID')
            ->addColumn(LogicInterface::LOGIC_CONDITION, Table::TYPE_TEXT, 20, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 'equal'
            ], 'Condition')
            ->addColumn(LogicInterface::ACTION, Table::TYPE_TEXT, 20, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 'show'
            ], 'Action')
            ->addColumn(LogicInterface::AGGREGATION, Table::TYPE_TEXT, 20, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 'any'
            ], 'Aggregation')
            ->addColumn(LogicInterface::IS_ACTIVE, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Active flag')
            ->addColumn(LogicInterface::VALUE_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Values in JSON')
            ->addColumn(LogicInterface::TARGET_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Targets in JSON')
            ->addColumn(LogicInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->addColumn(LogicInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT_UPDATE
            ], 'Last update time')
            ->setComment(
                'WebForms Logic'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, LogicInterface::FIELD_ID,
                    FieldTable::TABLE_NAME, FieldInterface::ID),
                LogicInterface::FIELD_ID,
                $setup->getTable(FieldTable::TABLE_NAME),
                FieldInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}
