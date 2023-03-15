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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class ResultTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_result';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(ResultInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(ResultInterface::FORM_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Form ID')
            ->addColumn(ResultInterface::STORE_ID, Table::TYPE_SMALLINT, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
            ], 'Store ID')
            ->addColumn(ResultInterface::CUSTOMER_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
            ], 'Customer ID')
            ->addColumn(ResultInterface::CUSTOMER_IP, Table::TYPE_TEXT, 255, [
            ], 'Customer IP')
            ->addColumn(ResultInterface::SUBMITTED_FROM_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Submitted from info in JSON')
            ->addColumn(ResultInterface::REFERRER_PAGE, Table::TYPE_TEXT, null, [
            ], 'Referrer page url')
            ->addColumn(ResultInterface::APPROVED, Table::TYPE_SMALLINT, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Approved status')
            ->addColumn(ResultInterface::IS_REPLIED, Table::TYPE_SMALLINT, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Is replied')
            ->addColumn(ResultInterface::IS_READ, Table::TYPE_SMALLINT, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Is read')
            ->addColumn(ResultInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->addColumn(ResultInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT_UPDATE
            ], 'Last update time')
            ->setComment(
                'WebForms Results'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, ResultInterface::FORM_ID,
                    FormTable::TABLE_NAME, FormInterface::ID),
                ResultInterface::FORM_ID,
                $setup->getTable(FormTable::TABLE_NAME),
                FormInterface::ID,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, ResultInterface::STORE_ID,
                    self::TABLE_STORE, self::COLUMN_STORE_ID),
                ResultInterface::STORE_ID,
                $setup->getTable(self::TABLE_STORE),
                self::COLUMN_STORE_ID,
                Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, ResultInterface::CUSTOMER_ID,
                    self::TABLE_CUSTOMER, self::COLUMN_CUSTOMER_ID),
                ResultInterface::CUSTOMER_ID,
                $setup->getTable(self::TABLE_CUSTOMER),
                self::COLUMN_CUSTOMER_ID,
                Table::ACTION_NO_ACTION
            );

    }
}
