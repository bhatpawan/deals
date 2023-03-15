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
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Config\Options\Field\DisplayOption;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class FieldTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_field';

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
            ->addColumn(FieldInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(FieldInterface::FORM_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Form ID')
            ->addColumn(FieldInterface::FIELDSET_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
            ], 'Fieldset ID')
            ->addColumn(FieldInterface::NAME, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Field Name')
            ->addColumn(FieldInterface::TYPE, Table::TYPE_TEXT, 100, [
                Table::OPTION_NULLABLE => false,
            ], 'Field type')
            ->addColumn(FieldInterface::CODE, Table::TYPE_TEXT, 255, [
            ], 'Field Code')
            ->addColumn(FieldInterface::RESULT_LABEL, Table::TYPE_TEXT, null, [
            ], 'Result label')
            ->addColumn(FieldInterface::COMMENT, Table::TYPE_TEXT, null, [
            ], 'Comment')
            ->addColumn(FieldInterface::TYPE_ATTRIBUTES_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Type attributes in JSON')
            ->addColumn(FieldInterface::IS_EMAIL_SUBJECT, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Use field as email subject')
            ->addColumn(FieldInterface::IS_REQUIRED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Required')
            ->addColumn(FieldInterface::VALIDATION_REQUIRED_MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Required message')
            ->addColumn(FieldInterface::POSITION, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Position')
            ->addColumn(FieldInterface::IS_ACTIVE, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Active flag')
            ->addColumn(FieldInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->addColumn(FieldInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT_UPDATE
            ], 'Last update time')
            /** Design */
            ->addColumn(FieldInterface::IS_LABEL_HIDDEN, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Hide Label')
            ->addColumn(FieldInterface::CUSTOM_ATTRIBUTES, Table::TYPE_TEXT, null, [
            ], 'Custom attributes')
            ->addColumn(FieldInterface::WIDTH_PROPORTION_LG, Table::TYPE_TEXT, null, [
            ], 'Large Screen Width')
            ->addColumn(FieldInterface::WIDTH_PROPORTION_MD, Table::TYPE_TEXT, null, [
            ], 'Medium Screen Width')
            ->addColumn(FieldInterface::WIDTH_PROPORTION_SM, Table::TYPE_TEXT, null, [
            ], 'Small Screen Width')
            ->addColumn(FieldInterface::IS_DISPLAYED_IN_NEW_ROW_LG, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Large screen start new row')
            ->addColumn(FieldInterface::IS_DISPLAYED_IN_NEW_ROW_MD, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Medium screen start new row')
            ->addColumn(FieldInterface::IS_DISPLAYED_IN_NEW_ROW_SM, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Small screen start new row')
            ->addColumn(FieldInterface::CSS_CONTAINER_CLASS, Table::TYPE_TEXT, null, [
            ], 'CSS class for container')
            ->addColumn(FieldInterface::CSS_INPUT_CLASS, Table::TYPE_TEXT, null, [
            ], 'CSS class for input')
            ->addColumn(FieldInterface::CSS_INPUT_STYLE, Table::TYPE_TEXT, null, [
            ], 'CSS style for input')
            ->addColumn(FieldInterface::DISPLAY_IN_RESULT, Table::TYPE_TEXT, 10, [
                Table::OPTION_DEFAULT => DisplayOption::OPTION_ON
            ], 'Display field in results overview and notifications')
            ->addColumn(FieldInterface::BROWSER_AUTOCOMPLETE, Table::TYPE_TEXT, null, [
            ], 'Browser Autocomplete')
            /** Validation */
            ->addColumn(FieldInterface::IS_UNIQUE, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Validate Unique')
            ->addColumn(FieldInterface::UNIQUE_VALIDATION_MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Validate Unique Message')
            ->addColumn(FieldInterface::MIN_LENGTH, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Minimum length')
            ->addColumn(FieldInterface::MIN_LENGTH_VALIDATION_MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Minimum length error message')
            ->addColumn(FieldInterface::MAX_LENGTH, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Maximum length')
            ->addColumn(FieldInterface::MAX_LENGTH_VALIDATION_MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Maximum length error message')
            ->addColumn(FieldInterface::REGEX_VALIDATION_PATTERN, Table::TYPE_TEXT, null, [
            ], 'Validation RegExp')
            ->addColumn(FieldInterface::REGEX_VALIDATION_MESSAGE, Table::TYPE_TEXT, null, [
            ], 'Validation RegExp error message')
            ->setComment(
                'WebForms Fields'
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, FieldInterface::FORM_ID,
                    FormTable::TABLE_NAME, FormInterface::ID),
                FieldInterface::FORM_ID,
                $setup->getTable(FormTable::TABLE_NAME),
                FormInterface::ID,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(self::TABLE_NAME, FieldInterface::FIELDSET_ID,
                    FieldsetTable::TABLE_NAME, FieldsetInterface::ID),
                FieldInterface::FIELDSET_ID,
                $setup->getTable(FieldsetTable::TABLE_NAME),
                FieldsetInterface::ID,
                Table::ACTION_SET_NULL
            );
    }
}
