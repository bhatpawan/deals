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

use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class TmpFileGalleryTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_tmp_file_gallery';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(TmpFileGalleryInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(TmpFileGalleryInterface::FIELD_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
            ], 'Field ID')
            ->addColumn(TmpFileGalleryInterface::NAME, Table::TYPE_TEXT, null, [
                Table::OPTION_NULLABLE => false,
            ], 'File Name')
            ->addColumn(TmpFileGalleryInterface::SIZE, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
            ], 'File Size')
            ->addColumn(TmpFileGalleryInterface::MIME_TYPE, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Mime Type')
            ->addColumn(TmpFileGalleryInterface::PATH, Table::TYPE_TEXT, null, [
                Table::OPTION_NULLABLE => false,
            ], 'File Path')
            ->addColumn(TmpFileGalleryInterface::HASH, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Hash')
            ->addColumn(TmpFileGalleryInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->setComment(
                'WebForms Gallery Temp Files'
            );
    }
}
