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
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\ResultValueInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class ResultValueTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_result_value';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(ResultValueInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(ResultValueInterface::RESULT_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Result ID')
            ->addColumn(ResultValueInterface::FIELD_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Field ID')
            ->addColumn(ResultValueInterface::VALUE, Table::TYPE_TEXT, null, [
            ], 'Value')
            ->setComment(
                'WebForms Results'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, ResultValueInterface::RESULT_ID,
                    ResultTable::TABLE_NAME, ResultInterface::ID),
                ResultValueInterface::RESULT_ID,
                $setup->getTable(ResultTable::TABLE_NAME),
                ResultInterface::ID,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, ResultValueInterface::FIELD_ID,
                    FieldTable::TABLE_NAME, FieldInterface::ID),
                ResultValueInterface::FIELD_ID,
                $setup->getTable(FieldTable::TABLE_NAME),
                FieldInterface::ID,
                Table::ACTION_CASCADE
            );
    }

}
