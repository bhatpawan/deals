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


use MageMe\WebForms\Api\Data\FileCustomerNotificationInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class FileCustomerNotificationTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_file_customer_notification';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(FileCustomerNotificationInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(FileCustomerNotificationInterface::FORM_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
            ], 'Form ID')
            ->addColumn(FileCustomerNotificationInterface::NAME, Table::TYPE_TEXT, null, [
                Table::OPTION_NULLABLE => false,
            ], 'File Name')
            ->addColumn(FileCustomerNotificationInterface::SIZE, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
            ], 'File Size')
            ->addColumn(FileCustomerNotificationInterface::MIME_TYPE, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Mime Type')
            ->addColumn(FileCustomerNotificationInterface::PATH, Table::TYPE_TEXT, null, [
                Table::OPTION_NULLABLE => false,
            ], 'File Path')
            ->addColumn(FileCustomerNotificationInterface::LINK_HASH, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Link Hash')
            ->addColumn(FileCustomerNotificationInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->setComment(
                'WebForms Customer Notification Files'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, FileCustomerNotificationInterface::FORM_ID,
                    FormTable::TABLE_NAME, FormInterface::ID),
                FileCustomerNotificationInterface::FORM_ID,
                $setup->getTable(FormTable::TABLE_NAME),
                FormInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}