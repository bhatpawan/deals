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

namespace MageMe\WebForms\Helper\ConvertVersion;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Setup\Table\FormTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FormConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const NAME = 'name';
    const CODE = 'code';
    const REDIRECT_URL = 'redirect_url';
    const DESCRIPTION = 'description';
    const SUCCESS_TEXT = 'success_text';
    const SEND_EMAIL = 'send_email';
    const ADD_HEADER = 'add_header';
    const DUPLICATE_EMAIL = 'duplicate_email';
    const EMAIL = 'email';
    const EMAIL_REPLY_TO = 'email_reply_to';
    const EMAIL_TEMPLATE_ID = 'email_template_id';
    const EMAIL_CUSTOMER_TEMPLATE_ID = 'email_customer_template_id';
    const EMAIL_REPLY_TEMPLATE_ID = 'email_reply_template_id';
    const EMAIL_RESULT_APPROVAL = 'email_result_approval';
    const EMAIL_RESULT_APPROVED_TEMPLATE_ID = 'email_result_approved_template_id';
    const EMAIL_RESULT_COMPLETED_TEMPLATE_ID = 'email_result_completed_template_id';
    const EMAIL_RESULT_NOTAPPROVED_TEMPLATE_ID = 'email_result_notapproved_template_id';
    const EMAIL_ATTACHMENTS_ADMIN = 'email_attachments_admin';
    const EMAIL_ATTACHMENTS_CUSTOMER = 'email_attachments_customer';
    const SURVEY = 'survey';
    const APPROVE = 'approve';
    const CAPTCHA_MODE = 'captcha_mode';
    const FILES_UPLOAD_LIMIT = 'files_upload_limit';
    const IMAGES_UPLOAD_LIMIT = 'images_upload_limit';
    const CREATED_TIME = 'created_time';
    const UPDATE_TIME = 'update_time';
    const IS_ACTIVE = 'is_active';
    const MENU = 'menu';
    const SUBMIT_BUTTON_TEXT = 'submit_button_text';
    const ACCESS_ENABLE = 'access_enable';
    const ACCESS_GROUPS_SERIALIZED = 'access_groups_serialized';
    const DASHBOARD_ENABLE = 'dashboard_enable';
    const DASHBOARD_GROUPS_SERIALIZED = 'dashboard_groups_serialized';
    const EMAIL_CUSTOMER_SENDER_NAME = 'email_customer_sender_name';
    const BCC_ADMIN_EMAIL = 'bcc_admin_email';
    const BCC_CUSTOMER_EMAIL = 'bcc_customer_email';
    const BCC_APPROVAL_EMAIL = 'bcc_approval_email';
    const ACCEPT_URL_PARAMETERS = 'accept_url_parameters';
    const FRONTEND_DOWNLOAD = 'frontend_download';
    const CUSTOMER_RESULT_PERMISSIONS_SERIALIZED = 'customer_result_permissions_serialized';
    const SHOW_WIDTH_LG = 'show_width_lg';
    const SHOW_WIDTH_MD = 'show_width_md';
    const SHOW_WIDTH_SM = 'show_width_sm';
    const URL_KEY = 'url_key';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const META_TITLE = 'meta_title';
    const BELOW_TEXT = 'below_text';

    const ACCESS_GROUPS = 'access_groups';
    const DASHBOARD_GROUPS = 'dashboard_groups';
    const CUSTOMER_RESULT_PERMISSIONS = 'customer_result_permissions';

    const DELETE_SUBMISSIONS = 'delete_submissions';
    const PURGE_ENABLE = 'purge_enable';
    const PURGE_PERIOD = 'purge_period';
    /**#@-*/

    const TABLE_WEBFORMS = 'webforms';

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;

    /**
     * FormConverter constructor.
     * @param FormRepositoryInterface $formRepository
     * @param FormFactory $formFactory
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        FormFactory             $formFactory
    )
    {
        $this->formFactory    = $formFactory;
        $this->formRepository = $formRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_WEBFORMS));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(FormTable::TABLE_NAME), [
                FormInterface::ID => $oldData[self::ID],
                FormInterface::NAME => $oldData[self::NAME]
            ]);
            $form = $this->formFactory->create();
            $form->setData($this->convertV2Data($oldData));
            $this->formRepository->save($form);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        $accessGroups              = $this->convertV2SerializedArray($oldData, self::ACCESS_GROUPS, self::ACCESS_GROUPS_SERIALIZED);
        $dashboardGroups           = $this->convertV2SerializedArray($oldData, self::DASHBOARD_GROUPS, self::DASHBOARD_GROUPS_SERIALIZED);
        $customerResultPermissions = $this->convertV2SerializedArray($oldData, self::CUSTOMER_RESULT_PERMISSIONS, self::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED);
        return [
            FormInterface::ID => $oldData[self::ID] ?? null,
            FormInterface::NAME => $oldData[self::NAME] ?? null,
            FormInterface::CODE => $oldData[self::CODE] ?? null,
            FormInterface::DESCRIPTION => $oldData[self::DESCRIPTION] ?? null,
            FormInterface::SUCCESS_TEXT => $oldData[self::SUCCESS_TEXT] ?? null,
            FormInterface::BELOW_TEXT => $oldData[self::BELOW_TEXT] ?? null,
            FormInterface::SUBMIT_BUTTON_TEXT => $oldData[self::SUBMIT_BUTTON_TEXT] ?? null,
            FormInterface::IS_MENU_LINK_ENABLED => $oldData[self::MENU] ?? null,
            FormInterface::IS_ACTIVE => $oldData[self::IS_ACTIVE] ?? null,
            FormInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
            FormInterface::UPDATED_AT => $oldData[self::UPDATE_TIME] ?? null,

            FormInterface::IS_URL_PARAMETERS_ACCEPTED => $oldData[self::ACCEPT_URL_PARAMETERS] ?? null,
            FormInterface::IS_SURVEY => $oldData[self::SURVEY] ?? null,
            FormInterface::REDIRECT_URL => $oldData[self::REDIRECT_URL] ?? null,
            FormInterface::IS_APPROVAL_CONTROLS_ENABLED => $oldData[self::APPROVE] ?? null,
            FormInterface::IS_APPROVAL_NOTIFICATION_ENABLED => $oldData[self::EMAIL_RESULT_APPROVAL] ?? null,
            FormInterface::APPROVAL_NOTIFICATION_BCC => $oldData[self::BCC_APPROVAL_EMAIL] ?? null,
            FormInterface::APPROVAL_NOTIFICATION_NOTAPPROVED_TEMPLATE_ID => $oldData[self::EMAIL_RESULT_NOTAPPROVED_TEMPLATE_ID] ?? null,
            FormInterface::APPROVAL_NOTIFICATION_APPROVED_TEMPLATE_ID => $oldData[self::EMAIL_RESULT_APPROVED_TEMPLATE_ID] ?? null,
            FormInterface::APPROVAL_NOTIFICATION_COMPLETED_TEMPLATE_ID => $oldData[self::EMAIL_RESULT_COMPLETED_TEMPLATE_ID] ?? null,
            FormInterface::CAPTCHA_MODE => $oldData[self::CAPTCHA_MODE] ?? null,
            FormInterface::FILES_UPLOAD_LIMIT => $oldData[self::FILES_UPLOAD_LIMIT] ?? null,
            FormInterface::IMAGES_UPLOAD_LIMIT => $oldData[self::IMAGES_UPLOAD_LIMIT] ?? null,

            FormInterface::IS_WIDTH_LG_SHOWED => $oldData[self::SHOW_WIDTH_LG] ?? null,
            FormInterface::IS_WIDTH_MD_SHOWED => $oldData[self::SHOW_WIDTH_MD] ?? null,
            FormInterface::IS_WIDTH_SM_SHOWED => $oldData[self::SHOW_WIDTH_SM] ?? null,

            FormInterface::IS_EMAIL_HEADER_ENABLED => $oldData[self::ADD_HEADER] ?? null,
            FormInterface::EMAIL_REPLY_TEMPLATE_ID => $oldData[self::EMAIL_REPLY_TEMPLATE_ID] ?? null,
            FormInterface::IS_ADMIN_NOTIFICATION_ENABLED => $oldData[self::SEND_EMAIL] ?? null,
            FormInterface::ADMIN_NOTIFICATION_TEMPLATE_ID => $oldData[self::EMAIL_TEMPLATE_ID] ?? null,
            FormInterface::ADMIN_NOTIFICATION_EMAIL => $oldData[self::EMAIL] ?? null,
            FormInterface::ADMIN_NOTIFICATION_BCC => $oldData[self::BCC_ADMIN_EMAIL] ?? null,
            FormInterface::IS_ADMIN_NOTIFICATION_ATTACHMENT_ENABLED => $oldData[self::EMAIL_ATTACHMENTS_ADMIN] ?? null,
            FormInterface::IS_CUSTOMER_NOTIFICATION_ENABLED => $oldData[self::DUPLICATE_EMAIL] ?? null,
            FormInterface::CUSTOMER_NOTIFICATION_TEMPLATE_ID => $oldData[self::EMAIL_CUSTOMER_TEMPLATE_ID] ?? null,
            FormInterface::CUSTOMER_NOTIFICATION_SENDER_NAME => $oldData[self::EMAIL_CUSTOMER_SENDER_NAME] ?? null,
            FormInterface::CUSTOMER_NOTIFICATION_REPLY_TO => $oldData[self::EMAIL_REPLY_TO] ?? null,
            FormInterface::CUSTOMER_NOTIFICATION_BCC => $oldData[self::BCC_CUSTOMER_EMAIL] ?? null,
            FormInterface::IS_CUSTOMER_NOTIFICATION_ATTACHMENT_ENABLED => $oldData[self::EMAIL_ATTACHMENTS_CUSTOMER] ?? null,

            FormInterface::IS_CUSTOMER_ACCESS_LIMITED => $oldData[self::ACCESS_ENABLE] ?? null,
            FormInterface::ACCESS_GROUPS_SERIALIZED => $oldData[self::ACCESS_GROUPS_SERIALIZED] ?? null,
            FormInterface::IS_CUSTOMER_DASHBOARD_ENABLED => $oldData[self::DASHBOARD_ENABLE] ?? null,
            FormInterface::DASHBOARD_GROUPS_SERIALIZED => $oldData[self::DASHBOARD_GROUPS_SERIALIZED] ?? null,
            FormInterface::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED => $oldData[self::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED] ?? null,
            FormInterface::IS_FRONTEND_DOWNLOAD_ALLOWED => $oldData[self::FRONTEND_DOWNLOAD] ?? null,

            FormInterface::URL_KEY => $oldData[self::URL_KEY] ?? null,
            FormInterface::META_TITLE => $oldData[self::META_TITLE] ?? null,
            FormInterface::META_KEYWORDS => $oldData[self::META_KEYWORDS] ?? null,
            FormInterface::META_DESCRIPTION => $oldData[self::META_DESCRIPTION] ?? null,

            FormInterface::IS_SUBMISSIONS_NOT_STORED => $oldData[self::DELETE_SUBMISSIONS] ?? null,
            FormInterface::IS_CLEANUP_ENABLED => $oldData[self::PURGE_ENABLE] ?? null,
            FormInterface::CLEANUP_PERIOD => $oldData[self::PURGE_PERIOD] ?? null,

            FormInterface::ACCESS_GROUPS => $accessGroups,
            FormInterface::DASHBOARD_GROUPS => $dashboardGroups,
            FormInterface::CUSTOMER_RESULT_PERMISSIONS => $customerResultPermissions
        ];
    }

    /**
     * @param array $oldData
     * @param string $field
     * @param string $serializedField
     * @return array
     */
    protected function convertV2SerializedArray(array $oldData, string $field, string $serializedField): ?array
    {
        $value = isset($oldData[$serializedField]) ? unserialize($oldData[$serializedField]) : null;
        if (!$value) {
            $value = (isset($oldData[$field]) && is_array($oldData[$field])) ? $oldData[$field] : [];
        }
        return $value;
    }

    /**
     * Convert V2 store data
     *
     * @param array $storeData
     * @return array
     */
    public function convertV2StoreData(array $storeData): array
    {
        $newData = [];
        foreach ($this->convertV2Data($storeData) as $key => $value) {
            if (!is_null($value)) {
                $newData[$key] = $value;
            }
        }
        $defaults = [
            FormInterface::ACCESS_GROUPS,
            FormInterface::DASHBOARD_GROUPS,
            FormInterface::CUSTOMER_RESULT_PERMISSIONS
        ];
        foreach ($defaults as $default) {
            if (!isset($storeData[$default])) {
                unset($newData[$default]);
            }
        }
        return $newData;
    }
}
