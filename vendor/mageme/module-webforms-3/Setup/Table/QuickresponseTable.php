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

use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\Data\QuickresponseInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class QuickresponseTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_quickresponse';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(QuickresponseInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(QuickresponseInterface::QUICKRESPONSE_CATEGORY_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
            ], 'Result ID')
            ->addColumn(QuickresponseInterface::TITLE, Table::TYPE_TEXT, null, [
            ], 'Title')
            ->addColumn(QuickresponseInterface::MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Message')
            ->addColumn(QuickresponseInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->addColumn(QuickresponseInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT_UPDATE
            ], 'Last update time')
            ->setComment(
                'WebForms Quickresponses'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, QuickresponseInterface::QUICKRESPONSE_CATEGORY_ID,
                    QuickresponseCategoryTable::TABLE_NAME, QuickresponseCategoryInterface::ID),
                QuickresponseInterface::QUICKRESPONSE_CATEGORY_ID,
                $setup->getTable(QuickresponseCategoryTable::TABLE_NAME),
                QuickresponseCategoryInterface::ID,
                Table::ACTION_SET_NULL
            );
    }
}
