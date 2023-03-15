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
use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class FileDropzoneTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_file_dropzone';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(FileDropzoneInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(FileDropzoneInterface::RESULT_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Result ID')
            ->addColumn(FileDropzoneInterface::FIELD_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Field ID')
            ->addColumn(FileDropzoneInterface::NAME, Table::TYPE_TEXT, null, [
                Table::OPTION_NULLABLE => false,
            ], 'File Name')
            ->addColumn(FileDropzoneInterface::SIZE, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
            ], 'File Size')
            ->addColumn(FileDropzoneInterface::MIME_TYPE, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Mime Type')
            ->addColumn(FileDropzoneInterface::PATH, Table::TYPE_TEXT, null, [
                Table::OPTION_NULLABLE => false,
            ], 'File Path')
            ->addColumn(FileDropzoneInterface::LINK_HASH, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Link Hash')
            ->addColumn(FileDropzoneInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->setComment(
                'WebForms Dropzone Files'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, FileDropzoneInterface::RESULT_ID,
                    ResultTable::TABLE_NAME, ResultInterface::ID),
                FileDropzoneInterface::RESULT_ID,
                $setup->getTable(ResultTable::TABLE_NAME),
                ResultInterface::ID,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, FileDropzoneInterface::FIELD_ID,
                    FieldTable::TABLE_NAME, FieldInterface::ID),
                FileDropzoneInterface::FIELD_ID,
                $setup->getTable(FieldTable::TABLE_NAME),
                FieldInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}
