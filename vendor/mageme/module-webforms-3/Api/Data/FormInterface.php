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

namespace MageMe\WebForms\Api\Data;


use Magento\Framework\Exception\LocalizedException;

interface FormInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    /** Information */
    const ID = 'form_id';
    const NAME = 'name';
    const CODE = 'code';
    const IS_MENU_LINK_ENABLED = 'is_menu_link_enabled';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /** Form Texts */
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const SUCCESS_TEXT = 'success_text';
    const BELOW_TEXT = 'below_text';
    const SUBMIT_BUTTON_TEXT = 'submit_button_text';

    /** General settings */
    const IS_URL_PARAMETERS_ACCEPTED = 'is_url_parameters_accepted';
    const IS_SURVEY = 'is_survey';
    const REDIRECT_URL = 'redirect_url';
    const IS_SUCCESS_SESSION_DISPLAYED = 'is_success_session_displayed';
    const IS_APPROVAL_CONTROLS_ENABLED = 'is_approval_controls_enabled';
    const IS_APPROVAL_NOTIFICATION_ENABLED = 'is_approval_notification_enabled';
    const APPROVAL_NOTIFICATION_BCC = 'approval_notification_bcc';
    const APPROVAL_NOTIFICATION_NOTAPPROVED_TEMPLATE_ID = 'approval_notification_notapproved_template_id';
    const APPROVAL_NOTIFICATION_APPROVED_TEMPLATE_ID = 'approval_notification_approved_template_id';
    const APPROVAL_NOTIFICATION_COMPLETED_TEMPLATE_ID = 'approval_notification_completed_template_id';
    const CAPTCHA_MODE = 'captcha_mode';
    const FILES_UPLOAD_LIMIT = 'files_upload_limit';
    const IMAGES_UPLOAD_LIMIT = 'images_upload_limit';

    /** Design settings */
    const IS_WIDTH_LG_SHOWED = 'is_width_lg_showed';
    const IS_WIDTH_MD_SHOWED = 'is_width_md_showed';
    const IS_WIDTH_SM_SHOWED = 'is_width_sm_showed';
    const TEMPLATE = 'template';
    const CSS_CLASS = 'css_class';
    const CSS_STYLE = 'css_style';
    const IS_DISPLAYED_AFTER_SUBMISSION = 'is_displayed_after_submission';
    const IS_SCROLLED_AFTER_SUBMISSION = 'is_scrolled_after_submission';
    const IS_ASYNC_LOADED = 'is_async_loaded';
    const SUBMIT_BUTTON_POSITION = 'submit_button_position';
    const SUBMIT_BUTTON_SIZE = 'submit_button_size';

    /** E-mail settings */
    const IS_EMAIL_HEADER_ENABLED = 'is_email_header_enabled';
    const EMAIL_REPLY_TEMPLATE_ID = 'email_reply_template_id';
    const IS_ADMIN_NOTIFICATION_ENABLED = 'is_admin_notification_enabled';
    const ADMIN_NOTIFICATION_TEMPLATE_ID = 'admin_notification_template_id';
    const ADMIN_NOTIFICATION_SENDER_NAME = 'admin_notification_sender_name';
    const ADMIN_NOTIFICATION_SENDER_EMAIL = 'admin_notification_sender_email';
    const ADMIN_NOTIFICATION_EMAIL = 'admin_notification_email';
    const ADMIN_NOTIFICATION_BCC = 'admin_notification_bcc';
    const IS_ADMIN_NOTIFICATION_ATTACHMENT_ENABLED = 'is_admin_notification_attachment_enabled';
    const IS_CUSTOMER_NOTIFICATION_ENABLED = 'is_customer_notification_enabled';
    const CUSTOMER_NOTIFICATION_TEMPLATE_ID = 'customer_notification_template_id';
    const CUSTOMER_NOTIFICATION_SENDER_NAME = 'customer_notification_sender_name';
    const CUSTOMER_NOTIFICATION_REPLY_TO = 'customer_notification_reply_to';
    const CUSTOMER_NOTIFICATION_BCC = 'customer_notification_bcc';
    const IS_CUSTOMER_NOTIFICATION_ATTACHMENT_ENABLED = 'is_customer_notification_attachment_enabled';
    const CUSTOMER_NOTIFICATION_ATTACHMENTS_SERIALIZED = 'customer_notification_attachments_serialized';

    /** Access settings */
    const IS_CUSTOMER_ACCESS_LIMITED = 'is_customer_access_limited';
    const ACCESS_GROUPS_SERIALIZED = 'access_groups_serialized';
    const IS_CUSTOMER_DASHBOARD_ENABLED = 'is_customer_dashboard_enabled';
    const DASHBOARD_GROUPS_SERIALIZED = 'dashboard_groups_serialized';
    const CUSTOMER_RESULT_PERMISSIONS_SERIALIZED = 'customer_result_permissions_serialized';
    const IS_FRONTEND_DOWNLOAD_ALLOWED = 'is_frontend_download_allowed';


    /** SEO settings */
    const URL_KEY = 'url_key';
    const META_TITLE = 'meta_title';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    /**#@-*/

    /** Data cleanup settings */
    const IS_SUBMISSIONS_NOT_STORED = 'is_submissions_not_stored';
    const IS_CLEANUP_ENABLED = 'is_cleanup_enabled';
    const CLEANUP_PERIOD = 'cleanup_period';
    /**#@-*/

    /** Form scripts */
    const ON_LOAD_SCRIPT = 'on_load_script';
    const AFTER_SUBMISSION_SCRIPT = 'after_submission_script';
    /**#@-*/

    /**#@+
     * Additional constants for keys of data array.
     */
    const ACCESS_GROUPS = 'access_groups';
    const DASHBOARD_GROUPS = 'dashboard_groups';
    const CUSTOMER_RESULT_PERMISSIONS = 'customer_result_permissions';
    const CUSTOMER_NOTIFICATION_ATTACHMENTS = 'customer_notification_attachments';

    const UNREAD_COUNT = 'unread_count';
    /**#@-*/


    #region Information
    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set ID
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): FormInterface;

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Set code
     *
     * @param string|null $code
     * @return $this
     */
    public function setCode(?string $code): FormInterface;

    /**
     * Get menu link flag
     *
     * @return bool
     */
    public function getIsMenuLinkEnabled(): bool;

    /**
     * Set menu link flag
     *
     * @param bool $isMenuLinkEnabled
     * @return $this
     */
    public function setIsMenuLinkEnabled(bool $isMenuLinkEnabled): FormInterface;

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * Set isActive
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): FormInterface;

    /**
     * Get createdTime
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set createdTime
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): FormInterface;

    /**
     * Get updateTime
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updateTime
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): FormInterface;
    #endregion

    #region Form texts
    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set title
     *
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): FormInterface;

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Set description
     *
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): FormInterface;

    /**
     * Get successText
     *
     * @return string|null
     */
    public function getSuccessText(): ?string;

    /**
     * Set successText
     *
     * @param string|null $successText
     * @return $this
     */
    public function setSuccessText(?string $successText): FormInterface;

    /**
     * Get belowText
     *
     * @return string|null
     */
    public function getBelowText(): ?string;

    /**
     * Set belowText
     *
     * @param string|null $belowText
     * @return $this
     */
    public function setBelowText(?string $belowText): FormInterface;

    /**
     * Get submitButtonText
     *
     * @return string|null
     */
    public function getSubmitButtonText(): ?string;

    /**
     * Set submitButtonText
     *
     * @param string|null $submitButtonText
     * @return $this
     */
    public function setSubmitButtonText(?string $submitButtonText): FormInterface;
    #endregion

    #region General settings
    /**
     * Get acceptUrlParameters flag
     *
     * @return bool
     */
    public function getIsUrlParametersAccepted(): bool;

    /**
     * Set acceptUrlParameters flag
     *
     * @param bool $isUrlParametersAccepted
     * @return $this
     */
    public function setIsUrlParametersAccepted(bool $isUrlParametersAccepted): FormInterface;

    /**
     * Get survey flag
     *
     * @return bool
     */
    public function getIsSurvey(): bool;

    /**
     * Set survey flag
     *
     * @param bool $survey
     * @return $this
     */
    public function setIsSurvey(bool $survey): FormInterface;

    /**
     * Get redirectUrl
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string;

    /**
     * Set redirectUrl
     *
     * @param string|null $redirectUrl
     * @return $this
     */
    public function setRedirectUrl(?string $redirectUrl): FormInterface;

    /**
     * Get IsSuccessSessionDisplayed
     *
     * @return bool
     */
    public function getIsSuccessSessionDisplayed(): bool;

    /**
     * Set IsSuccessSessionDisplayed
     *
     * @param bool $isSuccessSessionDisplayed
     * @return $this
     */
    public function setIsSuccessSessionDisplayed(bool $isSuccessSessionDisplayed): FormInterface;

    /**
     * Get approve
     *
     * @return bool
     */
    public function getIsApprovalControlsEnabled(): bool;

    /**
     * Set approve
     *
     * @param bool $isApprovalControlsEnabled
     * @return $this
     */
    public function setIsApprovalControlsEnabled(bool $isApprovalControlsEnabled): FormInterface;

    /**
     * Get emailResultApproval
     *
     * @return bool
     */
    public function getIsApprovalNotificationEnabled(): bool;

    /**
     * Set emailResultApproval
     *
     * @param bool $isApprovalNotificationEnabled
     * @return $this
     */
    public function setIsApprovalNotificationEnabled(bool $isApprovalNotificationEnabled): FormInterface;

    /**
     * Get bccApprovalEmail
     *
     * @return string
     */
    public function getApprovalNotificationBcc(): string;

    /**
     * Set bccApprovalEmail
     *
     * @param string|null $approvalNotificationBcc
     * @return $this
     */
    public function setApprovalNotificationBcc(?string $approvalNotificationBcc): FormInterface;

    /**
     * Get emailResultNotapprovedTemplateId
     *
     * @return int
     */
    public function getApprovalNotificationNotapprovedTemplateId(): int;

    /**
     * Set emailResultNotapprovedTemplateId
     *
     * @param int $approvalNotificationNotapprovedTemplateId
     * @return $this
     */
    public function setApprovalNotificationNotapprovedTemplateId(int $approvalNotificationNotapprovedTemplateId): FormInterface;

    /**
     * Get emailResultApprovedTemplateId
     *
     * @return int
     */
    public function getApprovalNotificationApprovedTemplateId(): int;

    /**
     * Set emailResultApprovedTemplateId
     *
     * @param int $approvalNotificationApprovedTemplateId
     * @return $this
     */
    public function setApprovalNotificationApprovedTemplateId(int $approvalNotificationApprovedTemplateId): FormInterface;

    /**
     * Get emailResultCompletedTemplateId
     *
     * @return int
     */
    public function getApprovalNotificationCompletedTemplateId(): int;

    /**
     * Set emailResultCompletedTemplateId
     *
     * @param int $approvalNotificationCompletedTemplateId
     * @return $this
     */
    public function setApprovalNotificationCompletedTemplateId(int $approvalNotificationCompletedTemplateId): FormInterface;

    /**
     * Get captchaMode
     *
     * @return string
     */
    public function getCaptchaMode(): string;

    /**
     * Set captchaMode
     *
     * @param string $captchaMode
     * @return $this
     */
    public function setCaptchaMode(string $captchaMode): FormInterface;

    /**
     * Get filesUploadLimit
     *
     * @return int|null
     */
    public function getFilesUploadLimit(): ?int;

    /**
     * Set filesUploadLimit
     *
     * @param int|null $filesUploadLimit
     * @return $this
     */
    public function setFilesUploadLimit(?int $filesUploadLimit): FormInterface;

    /**
     * Get imagesUploadLimit
     *
     * @return int|null
     */
    public function getImagesUploadLimit(): ?int;

    /**
     * Set imagesUploadLimit
     *
     * @param int|null $imagesUploadLimit
     * @return $this
     */
    public function setImagesUploadLimit(?int $imagesUploadLimit): FormInterface;
    #endregion

    #region Design settings
    /**
     * Get showWidthLg
     *
     * @return bool
     */
    public function getIsWidthLgShowed(): bool;

    /**
     * Set showWidthLg
     *
     * @param bool $isWidthLgShowed
     * @return $this
     */
    public function setIsWidthLgShowed(bool $isWidthLgShowed): FormInterface;

    /**
     * Get showWidthMd
     *
     * @return bool
     */
    public function getIsWidthMdShowed(): bool;

    /**
     * Set showWidthMd
     *
     * @param bool $isWidthMdShowed
     * @return $this
     */
    public function setIsWidthMdShowed(bool $isWidthMdShowed): FormInterface;

    /**
     * Get showWidthSm
     *
     * @return bool
     */
    public function getIsWidthSmShowed(): bool;

    /**
     * Set showWidthSm
     *
     * @param bool $isWidthSmShowed
     * @return $this
     */
    public function setIsWidthSmShowed(bool $isWidthSmShowed): FormInterface;

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate(): string;

    /**
     * Set template
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): FormInterface;

    /**
     * Get CSS class
     *
     * @return string|null
     */
    public function getCssClass(): ?string;

    /**
     * Set CSS class
     *
     * @param string|null $cssClass
     * @return $this
     */
    public function setCssClass(?string $cssClass): FormInterface;

    /**
     * Get CSS style
     *
     * @return string|null
     */
    public function getCssStyle(): ?string;

    /**
     * Set CSS style
     *
     * @param string|null $cssStyle
     * @return $this
     */
    public function setCssStyle(?string $cssStyle): FormInterface;

    /**
     * Get is_displayed_after_submission
     *
     * @return bool
     */
    public function getIsDisplayedAfterSubmission(): bool;

    /**
     * Set is_displayed_after_submission
     *
     * @param bool $isDisplayed
     * @return $this
     */
    public function setIsDisplayedAfterSubmission(bool $isDisplayed): FormInterface;

    /**
     * Get is_scrolled_after_submission
     *
     * @return bool
     */
    public function getIsScrolledAfterSubmission(): bool;

    /**
     * Set is_scrolled_after_submission
     *
     * @param bool $isScrolled
     * @return $this
     */
    public function setIsScrolledAfterSubmission(bool $isScrolled): FormInterface;

    /**
     * Get is_async_loaded
     *
     * @return bool
     */
    public function getIsAsyncLoaded(): bool;

    /**
     * Set is_async_loaded
     *
     * @param bool $isAsyncLoaded
     * @return $this
     */
    public function setIsAsyncLoaded(bool $isAsyncLoaded): FormInterface;

    /**
     * Set submitButtonPosition
     *
     * @param string|null $submitButtonPosition
     * @return $this
     */
    public function setSubmitButtonPosition(?string $submitButtonPosition): FormInterface;

    /**
     * Get submitButtonPosition
     *
     * @return string|null
     */
    public function getSubmitButtonPosition(): ?string;

    /**
     * Get submitButtonSize
     *
     * @return string|null
     */
    public function getSubmitButtonSize(): ?string;

    /**
     * Set submitButtonSize
     *
     * @param string|null $submitButtonSize
     * @return $this
     */
    public function setSubmitButtonSize(?string $submitButtonSize): FormInterface;
    #endregion

    #region E-mail settings
    /**
     * Get addHeader
     *
     * @return bool
     */
    public function getIsEmailHeaderEnabled(): bool;

    /**
     * Set addHeader
     *
     * @param bool $isEmailHeaderEnabled
     * @return $this
     */
    public function setIsEmailHeaderEnabled(bool $isEmailHeaderEnabled): FormInterface;

    /**
     * Get emailReplyTemplateId
     *
     * @return int|null
     */
    public function getEmailReplyTemplateId(): ?int;

    /**
     * Set emailReplyTemplateId
     *
     * @param int|null $emailReplyTemplateId
     * @return $this
     */
    public function setEmailReplyTemplateId(?int $emailReplyTemplateId): FormInterface;

    /**
     * Get sendEmail
     *
     * @return bool
     */
    public function getIsAdminNotificationEnabled(): bool;

    /**
     * Set sendEmail
     *
     * @param bool $isAdminNotificationEnabled
     * @return $this
     */
    public function setIsAdminNotificationEnabled(bool $isAdminNotificationEnabled): FormInterface;

    /**
     * Get emailTemplateId
     *
     * @return int|null
     */
    public function getAdminNotificationTemplateId(): ?int;

    /**
     * Set emailTemplateId
     *
     * @param int|null $adminNotificationTemplateId
     * @return $this
     */
    public function setAdminNotificationTemplateId(?int $adminNotificationTemplateId): FormInterface;

    /**
     * Get adminNotificationSenderName
     *
     * @return string
     */
    public function getAdminNotificationSenderName(): string;

    /**
     * Set adminNotificationSenderName
     *
     * @param string|null $adminNotificationSenderName
     * @return $this
     */
    public function setAdminNotificationSenderName(?string $adminNotificationSenderName): FormInterface;

    /**
     * Get adminNotificationSenderEmail
     *
     * @return string|null
     */
    public function getAdminNotificationSenderEmail(): ?string;

    /**
     * Set adminNotificationSenderEmail
     *
     * @param string|null $adminNotificationSenderEmail
     * @return $this
     */
    public function setAdminNotificationSenderEmail(?string $adminNotificationSenderEmail): FormInterface;

    /**
     * Get email
     *
     * @return string|null
     */
    public function getAdminNotificationEmail(): ?string;

    /**
     * Set email
     *
     * @param string|null $adminNotificationEmail
     * @return $this
     */
    public function setAdminNotificationEmail(?string $adminNotificationEmail): FormInterface;

    /**
     * Get bccAdminEmail
     *
     * @return string|null
     */
    public function getAdminNotificationBcc(): ?string;

    /**
     * Set bccAdminEmail
     *
     * @param string|null $adminNotificationBcc
     * @return $this
     */
    public function setAdminNotificationBcc(?string $adminNotificationBcc): FormInterface;

    /**
     * Get emailAttachmentsAdmin
     *
     * @return bool
     */
    public function getIsAdminNotificationAttachmentEnabled(): bool;

    /**
     * Set emailAttachmentsAdmin
     *
     * @param bool $isAdminNotificationAttachmentEnabled
     * @return $this
     */
    public function setIsAdminNotificationAttachmentEnabled(bool $isAdminNotificationAttachmentEnabled): FormInterface;

    /**
     * Get duplicateEmail
     *
     * @return bool
     */
    public function getIsCustomerNotificationEnabled(): bool;

    /**
     * Set duplicateEmail
     *
     * @param bool $isCustomerNotificationEnabled
     * @return $this
     */
    public function setIsCustomerNotificationEnabled(bool $isCustomerNotificationEnabled): FormInterface;

    /**
     * Get emailCustomerTemplateId
     *
     * @return int|null
     */
    public function getCustomerNotificationTemplateId(): ?int;

    /**
     * Set emailCustomerTemplateId
     *
     * @param int|null $customerNotificationTemplateId
     * @return $this
     */
    public function setCustomerNotificationTemplateId(?int $customerNotificationTemplateId): FormInterface;

    /**
     * Get emailCustomerSenderName
     *
     * @return string
     */
    public function getCustomerNotificationSenderName(): string;

    /**
     * Set emailCustomerSenderName
     *
     * @param string|null $customerNotificationSenderName
     * @return $this
     */
    public function setCustomerNotificationSenderName(?string $customerNotificationSenderName): FormInterface;

    /**
     * Get emailReplyTo
     *
     * @return string|null
     */
    public function getCustomerNotificationReplyTo(): ?string;

    /**
     * Set emailReplyTo
     *
     * @param string|null $customerNotificationReplyTo
     * @return $this
     */
    public function setCustomerNotificationReplyTo(?string $customerNotificationReplyTo): FormInterface;

    /**
     * Get bccCustomerEmail
     *
     * @return string|null
     */
    public function getCustomerNotificationBcc(): ?string;

    /**
     * Set bccCustomerEmail
     *
     * @param string|null $customerNotificationBcc
     * @return $this
     */
    public function setCustomerNotificationBcc(?string $customerNotificationBcc): FormInterface;

    /**
     * Get emailAttachmentsCustomer
     *
     * @return bool
     */
    public function getIsCustomerNotificationAttachmentEnabled(): bool;

    /**
     * Set emailAttachmentsCustomer
     *
     * @param bool $isCustomerNotificationAttachmentEnabled
     * @return $this
     */
    public function setIsCustomerNotificationAttachmentEnabled(bool $isCustomerNotificationAttachmentEnabled): FormInterface;
    #endregion

    #region Access settings
    /**
     * Get accessEnable
     *
     * @return bool
     */
    public function getIsCustomerAccessLimited(): bool;

    /**
     * Set accessEnable
     *
     * @param bool $isCustomerAccessLimited
     * @return $this
     */
    public function setIsCustomerAccessLimited(bool $isCustomerAccessLimited): FormInterface;

    /**
     * Get accessGroupsSerialized
     *
     * @return string|null
     */
    public function getAccessGroupsSerialized(): ?string;

    /**
     * Set accessGroupsSerialized
     *
     * @param string|null $accessGroupsSerialized
     * @return $this
     */
    public function setAccessGroupsSerialized(?string $accessGroupsSerialized): FormInterface;

    /**
     * Get dashboardEnable
     *
     * @return bool
     */
    public function getIsCustomerDashboardEnabled(): bool;

    /**
     * Set dashboardEnable
     *
     * @param bool $isCustomerDashboardEnabled
     * @return $this
     */
    public function setIsCustomerDashboardEnabled(bool $isCustomerDashboardEnabled): FormInterface;

    /**
     * Get dashboardGroupsSerialized
     *
     * @return string|null
     */
    public function getDashboardGroupsSerialized(): ?string;

    /**
     * Set dashboardGroupsSerialized
     *
     * @param string|null $dashboardGroupsSerialized
     * @return $this
     */
    public function setDashboardGroupsSerialized(?string $dashboardGroupsSerialized): FormInterface;

    /**
     * Get customerResultPermissionsSerialized
     *
     * @return string|null
     */
    public function getCustomerResultPermissionsSerialized(): ?string;

    /**
     * Set customerResultPermissionsSerialized
     *
     * @param string|null $customerResultPermissionsSerialized
     * @return $this
     */
    public function setCustomerResultPermissionsSerialized(?string $customerResultPermissionsSerialized): FormInterface;

    /**
     * Get customerNotificationAttachmentsSerialized
     *
     * @return string|null
     */
    public function getCustomerNotificationAttachmentsSerialized(): ?string;

    /**
     * Set customerNotificationAttachmentsSerialized
     *
     * @param string|null $customerNotificationAttachmentsSerialized
     * @return $this
     */
    public function setCustomerNotificationAttachmentsSerialized(?string $customerNotificationAttachmentsSerialized): FormInterface;

    /**
     * Get frontendDownload
     *
     * @return bool
     */
    public function getIsFrontendDownloadAllowed(): bool;

    /**
     * Set frontendDownload
     *
     * @param bool $isFrontendDownloadAllowed
     * @return $this
     */
    public function setIsFrontendDownloadAllowed(bool $isFrontendDownloadAllowed): FormInterface;
    #endregion


    #region SEO settings
    /**
     * Get URL key for rewrite
     *
     * @return string|null
     */
    public function getUrlKey(): ?string;

    /**
     * Set URL key for rewrite
     *
     * @param string|null $urlKey
     * @return $this
     */
    public function setUrlKey(?string $urlKey): FormInterface;

    /**
     * Get metaTitle
     *
     * @return string|null
     */
    public function getMetaTitle(): ?string;

    /**
     * Set metaTitle
     *
     * @param string|null $metaTitle
     * @return $this
     */
    public function setMetaTitle(?string $metaTitle): FormInterface;

    /**
     * Get metaKeywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): ?string;

    /**
     * Set metaKeywords
     *
     * @param string|null $metaKeywords
     * @return $this
     */
    public function setMetaKeywords(?string $metaKeywords): FormInterface;

    /**
     * Get metaDescription
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string;

    /**
     * Set metaDescription
     *
     * @param string|null $metaDescription
     * @return $this
     */
    public function setMetaDescription(?string $metaDescription): FormInterface;
    #endregion

    #region Form scripts
    /**
     * Get on_load_script
     *
     * @return string|null
     */
    public function getOnLoadScript(): ?string;

    /**
     * Set on_load_script
     *
     * @param string|null $script
     * @return $this
     */
    public function setOnLoadScript(?string $script): FormInterface;

    /**
     * Get after_submission_script
     *
     * @return string|null
     */
    public function getAfterSubmissionScript(): ?string;

    /**
     * Set after_submission_script
     *
     * @param string|null $script
     * @return $this
     */
    public function setAfterSubmissionScript(?string $script): FormInterface;
    #endregion

    /**
     * Get dashboard groups
     *
     * @return array
     */
    public function getDashboardGroups(): array;

    /**
     * Set dashboard groups
     *
     * @param array $dashboardGroups
     * @return $this
     */
    public function setDashboardGroups(array $dashboardGroups): FormInterface;

    /**
     * Get access groups
     *
     * @return array
     */
    public function getAccessGroups(): array;

    /**
     * Set access groups
     *
     * @param array $accessGroups
     * @return $this
     */
    public function setAccessGroups(array $accessGroups): FormInterface;

    /**
     * Get customer result permissions
     *
     * @return array
     */
    public function getCustomerResultPermissions(): array;

    /**
     * Set customer result permissions
     *
     * @param array $customerResultPermissions
     * @return mixed
     */
    public function setCustomerResultPermissions(array $customerResultPermissions);

    /**
     * Get customer notification attachments
     *
     * @return array
     */
    public function getCustomerNotificationAttachments(): array;

    /**
     * Set customer notification attachments
     *
     * @param array $customerNotificationAttachments
     * @return mixed
     */
    public function setCustomerNotificationAttachments(array $customerNotificationAttachments);

    /**
     * Clone form
     *
     * @return $this
     */
    public function duplicate(): FormInterface;

    /**
     * Clone form with new parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function clone(array $parameters = []): FormInterface;

    /**
     * Get form results
     *
     * @return ResultInterface[]
     * @throws LocalizedException
     */
    public function getResults(): array;

    /**
     * @return bool
     */
    public function getIsSubmissionNotStored(): bool;

    /**
     * @param bool $isSubmissionNotStored
     * @return $this
     */
    public function setIsSubmissionNotStored(bool $isSubmissionNotStored): FormInterface;

    /**
     * @return bool
     */
    public function getIsCleanupEnabled(): bool;

    /**
     * @param bool $isCleanupEnabled
     * @return $this
     */
    public function setIsCleanupEnabled(bool $isCleanupEnabled): FormInterface;


    /**
     * @return int|null
     */
    public function getCleanupPeriod(): ?int;

    /**
     * @param int $cleanupPeriod
     * @return $this
     */
    public function setCleanupPeriod(int $cleanupPeriod): FormInterface;

    /**
     * @return int
     */
    public function getUnreadCount(): int;

    /**
     * @param int $unreadCount
     * @return FormInterface
     */
    public function setUnreadCount(int $unreadCount): FormInterface;
}
