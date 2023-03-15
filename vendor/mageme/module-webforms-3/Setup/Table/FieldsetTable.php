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

use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class FieldsetTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_fieldset';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            /** Information */
            ->addColumn(FieldsetInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(FieldsetInterface::FORM_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Form ID')
            ->addColumn(FieldsetInterface::NAME, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false
            ], 'Fieldset Name')
            ->addColumn(FieldsetInterface::POSITION, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Position')
            ->addColumn(FieldsetInterface::IS_ACTIVE, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Active flag')
            ->addColumn(FieldsetInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->addColumn(FieldsetInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT_UPDATE
            ], 'Last update time')
            /** Design */
            ->addColumn(FieldsetInterface::WIDTH_PROPORTION_LG, Table::TYPE_TEXT, null, [
            ], 'Large Screen Width')
            ->addColumn(FieldsetInterface::WIDTH_PROPORTION_MD, Table::TYPE_TEXT, null, [
            ], 'Medium Screen Width')
            ->addColumn(FieldsetInterface::WIDTH_PROPORTION_SM, Table::TYPE_TEXT, null, [
            ], 'Small Screen Width')
            ->addColumn(FieldsetInterface::IS_DISPLAYED_IN_NEW_ROW_LG, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Large screen start new row')
            ->addColumn(FieldsetInterface::IS_DISPLAYED_IN_NEW_ROW_MD, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Medium screen start new row')
            ->addColumn(FieldsetInterface::IS_DISPLAYED_IN_NEW_ROW_SM, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Small screen start new row')
            /** CSS */
            ->addColumn(FieldsetInterface::CSS_CLASS, Table::TYPE_TEXT, null, [
            ], 'CSS class')
            ->addColumn(FieldsetInterface::CSS_STYLE, Table::TYPE_TEXT, null, [
            ], 'CSS Style')
            /** Results / Notifications Settings */
            ->addColumn(FieldsetInterface::IS_NAME_DISPLAYED_IN_RESULT, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 1
            ], 'Display fieldset name in results overview and notifications')
            ->setComment(
                'WebForms Fieldsets'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, FieldsetInterface::FORM_ID,
                    FormTable::TABLE_NAME, FormInterface::ID),
                FieldsetInterface::FORM_ID,
                $setup->getTable(FormTable::TABLE_NAME),
                FormInterface::ID,
                Table::ACTION_CASCADE
            );

    }
}
