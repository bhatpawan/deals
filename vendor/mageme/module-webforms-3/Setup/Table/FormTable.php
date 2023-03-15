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
use MageMe\WebForms\Config\Options\Form\Template;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class FormTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_form';

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
            ->addColumn(FormInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'ID')
            ->addColumn(FormInterface::NAME, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Form Name')
            ->addColumn(FormInterface::CODE, Table::TYPE_TEXT, 255, [
            ], 'Form Code')
            ->addColumn(FormInterface::IS_MENU_LINK_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Add form link to menu')
            ->addColumn(FormInterface::IS_ACTIVE, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Active flag')
            ->addColumn(FormInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT
            ], 'Created time')
            ->addColumn(FormInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Table::TIMESTAMP_INIT_UPDATE
            ], 'Last update time')
            /** Form Texts */
            ->addColumn(FormInterface::TITLE, Table::TYPE_TEXT, null, [
            ], 'Title')
            ->addColumn(FormInterface::DESCRIPTION, Table::TYPE_TEXT, null, [
            ], 'Description')
            ->addColumn(FormInterface::SUCCESS_TEXT, Table::TYPE_TEXT, null, [
            ], 'Success Text')
            ->addColumn(FormInterface::BELOW_TEXT, Table::TYPE_TEXT, null, [
            ], 'Text below the form')
            ->addColumn(FormInterface::SUBMIT_BUTTON_TEXT, Table::TYPE_TEXT, 255, [
            ], 'Submit button text')
            /** General settings */
            ->addColumn(FormInterface::IS_URL_PARAMETERS_ACCEPTED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Accept URL parameters')
            ->addColumn(FormInterface::IS_SURVEY, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Survey mode')
            ->addColumn(FormInterface::REDIRECT_URL, Table::TYPE_TEXT, null, [
            ], 'Redirect URL')
            ->addColumn(FormInterface::IS_APPROVAL_CONTROLS_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Enable results approval')
            ->addColumn(FormInterface::IS_APPROVAL_NOTIFICATION_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Enable result approval')
            ->addColumn(FormInterface::APPROVAL_NOTIFICATION_BCC, Table::TYPE_TEXT, 255, [
            ], 'BCC Approval Email')
            ->addColumn(FormInterface::APPROVAL_NOTIFICATION_NOTAPPROVED_TEMPLATE_ID, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Not approved result notification template')
            ->addColumn(FormInterface::APPROVAL_NOTIFICATION_APPROVED_TEMPLATE_ID, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Approved result notification template')
            ->addColumn(FormInterface::APPROVAL_NOTIFICATION_COMPLETED_TEMPLATE_ID, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Completed result notification template')
            ->addColumn(FormInterface::CAPTCHA_MODE, Table::TYPE_TEXT, 40, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 'default'
            ], 'Captcha mode')
            ->addColumn(FormInterface::FILES_UPLOAD_LIMIT, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Files upload limit')
            ->addColumn(FormInterface::IMAGES_UPLOAD_LIMIT, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Images upload limit')
            /** Design settings */
            ->addColumn(FormInterface::TEMPLATE, Table::TYPE_TEXT, 100, [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => Template::DEFAULT
            ], 'From template')
            ->addColumn(FormInterface::CSS_CLASS, Table::TYPE_TEXT, null, [
            ], 'CSS class')
            ->addColumn(FormInterface::CSS_STYLE, Table::TYPE_TEXT, null, [
            ], 'CSS Style')
            ->addColumn(FormInterface::IS_DISPLAYED_AFTER_SUBMISSION, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Display form after successful submission')
            ->addColumn(FormInterface::IS_SCROLLED_AFTER_SUBMISSION, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Scroll to the top of success message after form submission')
            ->addColumn(FormInterface::IS_ASYNC_LOADED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Load form asynchronously with ajax request to fix full page caching issues')
            ->addColumn(FormInterface::IS_WIDTH_LG_SHOWED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Large screen width controls')
            ->addColumn(FormInterface::IS_WIDTH_MD_SHOWED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Medium screen width controls')
            ->addColumn(FormInterface::IS_WIDTH_SM_SHOWED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Small screen width controls')
            ->addColumn(FormInterface::SUBMIT_BUTTON_POSITION, Table::TYPE_TEXT, 255, [
            ], 'Submit button position')
            ->addColumn(FormInterface::SUBMIT_BUTTON_SIZE, Table::TYPE_TEXT, 255, [
            ], 'Submit button size')
            /** E-mail settings */
            ->addColumn(FormInterface::IS_EMAIL_HEADER_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Add header to the e-mail')
            ->addColumn(FormInterface::EMAIL_REPLY_TEMPLATE_ID, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
            ], 'Reply template')
            ->addColumn(FormInterface::IS_ADMIN_NOTIFICATION_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Send admin notification')
            ->addColumn(FormInterface::ADMIN_NOTIFICATION_TEMPLATE_ID, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
            ], 'Admin notification template')
            ->addColumn(FormInterface::ADMIN_NOTIFICATION_EMAIL, Table::TYPE_TEXT, 255, [
            ], 'Admin notification e-mail address')
            ->addColumn(FormInterface::ADMIN_NOTIFICATION_BCC, Table::TYPE_TEXT, 255, [
            ], 'Blind carbon copy of customer notification')
            ->addColumn(FormInterface::IS_ADMIN_NOTIFICATION_ATTACHMENT_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Attach files to admin notifications')
            ->addColumn(FormInterface::IS_CUSTOMER_NOTIFICATION_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Send customer notification')
            ->addColumn(FormInterface::CUSTOMER_NOTIFICATION_TEMPLATE_ID, Table::TYPE_INTEGER, 11, [
                Table::OPTION_UNSIGNED => true,
            ], 'Customer notification template')
            ->addColumn(FormInterface::CUSTOMER_NOTIFICATION_SENDER_NAME, Table::TYPE_TEXT, 255, [
            ], 'Sender name for customer email')
            ->addColumn(FormInterface::CUSTOMER_NOTIFICATION_REPLY_TO, Table::TYPE_TEXT, null, [
            ], 'Reply-to e-mail address for customer')
            ->addColumn(FormInterface::CUSTOMER_NOTIFICATION_BCC, Table::TYPE_TEXT, 255, [
            ], 'Blind carbon copy of customer notification')
            ->addColumn(FormInterface::IS_CUSTOMER_NOTIFICATION_ATTACHMENT_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Attach files to customer notifications')
            ->addColumn(FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Attached files for customer in JSON')
            /** Access settings */
            ->addColumn(FormInterface::IS_CUSTOMER_ACCESS_LIMITED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Enable access controls')
            ->addColumn(FormInterface::ACCESS_GROUPS_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Access groups in JSON')
            ->addColumn(FormInterface::IS_CUSTOMER_DASHBOARD_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Enable customer dashboard controls')
            ->addColumn(FormInterface::DASHBOARD_GROUPS_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Dashboard groups in JSON')
            ->addColumn(FormInterface::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED, Table::TYPE_TEXT, null, [
            ], 'Customer Result Permissions in JSON')
            ->addColumn(FormInterface::IS_FRONTEND_DOWNLOAD_ALLOWED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Frontend Download')
            /** SEO settings */
            ->addColumn(FormInterface::URL_KEY, Table::TYPE_TEXT, null, [
            ], 'Key for URL rewrite')
            ->addColumn(FormInterface::META_TITLE, Table::TYPE_TEXT, 255, [
            ], 'View page meta title')
            ->addColumn(FormInterface::META_KEYWORDS, Table::TYPE_TEXT, null, [
            ], 'View page meta keywords')
            ->addColumn(FormInterface::META_DESCRIPTION, Table::TYPE_TEXT, null, [
            ], 'View page meta description')
            /** Data cleanup settings */
            ->addColumn(FormInterface::IS_SUBMISSIONS_NOT_STORED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Do not store submissions')
            ->addColumn(FormInterface::IS_CLEANUP_ENABLED, Table::TYPE_BOOLEAN, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0
            ], 'Enable automatic cleanup')
            ->addColumn(FormInterface::CLEANUP_PERIOD, Table::TYPE_INTEGER, 10, [
            ], 'Cleanup period')
            /** Form Texts */
            ->addColumn(FormInterface::ON_LOAD_SCRIPT, Table::TYPE_TEXT, null, [
            ], 'On load script')
            ->addColumn(FormInterface::AFTER_SUBMISSION_SCRIPT, Table::TYPE_TEXT, null, [
            ], 'After submission script')
            ->setComment(
                'WebForms Forms'
            );
    }
}
