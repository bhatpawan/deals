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

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class MessageTable extends AbstractTable
{

    const TABLE_NAME = 'mm_webforms_message';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(MessageInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(MessageInterface::RESULT_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Result ID')
            ->addColumn(MessageInterface::USER_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
            ], 'User ID')
            ->addColumn(MessageInterface::MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Message')
            ->addColumn(MessageInterface::AUTHOR, Table::TYPE_TEXT, 100, [
            ], 'Author')
            ->addColumn(MessageInterface::IS_CUSTOMER_EMAILED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Customer e-mailed')
            ->addColumn(MessageInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->setComment(
                'WebForms Message'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, MessageInterface::RESULT_ID,
                    ResultTable::TABLE_NAME, ResultInterface::ID),
                MessageInterface::RESULT_ID,
                $setup->getTable(ResultTable::TABLE_NAME),
                ResultInterface::ID,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, MessageInterface::USER_ID,
                    self::TABLE_USER, self::COLUMN_USER_ID),
                MessageInterface::USER_ID,
                $setup->getTable(self::TABLE_USER),
                self::COLUMN_USER_ID,
                Table::ACTION_SET_NULL
            );

    }

}
