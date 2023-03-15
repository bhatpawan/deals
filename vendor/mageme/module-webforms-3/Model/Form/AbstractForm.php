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

namespace MageMe\WebForms\Model\Form;


use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Config\Options\Form\Template;
use MageMe\WebForms\Model\AbstractModel;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\FieldsetFactory;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\Logic;
use MageMe\WebForms\Model\LogicFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractForm extends AbstractModel implements FormInterface
{
    /**
     * Form cache tag
     */
    const CACHE_TAG = 'webforms_form';

    const PATH_ADMIN_NOTIFICATION_EMAIL = 'webforms/email/email';

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var LogicFactory
     */
    protected $logicFactory;

    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * AbstractForm constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct(
            $context->getStoreRepository(),
            $context->getStoreFactory(),
            $context->getContext(),
            $context->getRegistry(),
            $context->getResource(),
            $context->getResourceCollection(),
            $context->getData());
        $this->filterBuilder         = $context->getFilterBuilder();
        $this->sortOrderBuilder      = $context->getSortOrderBuilder();
        $this->searchCriteriaBuilder = $context->getSearchCriteriaBuilder();
        $this->formFactory           = $context->getFormFactory();
        $this->formRepository        = $context->getFormRepository();
        $this->logicFactory          = $context->getLogicFactory();
        $this->logicRepository       = $context->getLogicRepository();
        $this->fieldsetFactory       = $context->getFieldsetFactory();
        $this->fieldsetRepository    = $context->getFieldsetRepository();
        $this->fieldFactory          = $context->getFieldFactory();
        $this->fieldRepository       = $context->getFieldRepository();
        $this->scopeConfig           = $context->getScopeConfig();
        $this->session               = $context->getSession();
        $this->dateHelper            = $context->getDateHelper();
    }

#region DB getters and setters

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): FormInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode(?string $code): FormInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrl(): ?string
    {
        return $this->getData(self::REDIRECT_URL);
    }

    /**
     * @inheritDoc
     */
    public function setRedirectUrl(?string $redirectUrl): FormInterface
    {
        return $this->setData(self::REDIRECT_URL, $redirectUrl);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle(?string $title): FormInterface
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription(?string $description): FormInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getSuccessText(): ?string
    {
        return $this->getData(self::SUCCESS_TEXT);
    }

    /**
     * @inheritDoc
     */
    public function setSuccessText(?string $successText): FormInterface
    {
        return $this->setData(self::SUCCESS_TEXT, $successText);
    }

    /**
     * @inheritDoc
     */
    public function getIsAdminNotificationEnabled(): bool
    {
        return (bool)$this->getData(self::IS_ADMIN_NOTIFICATION_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsAdminNotificationEnabled(bool $isAdminNotificationEnabled): FormInterface
    {
        return $this->setData(self::IS_ADMIN_NOTIFICATION_ENABLED, $isAdminNotificationEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getIsEmailHeaderEnabled(): bool
    {
        return (bool)$this->getData(self::IS_EMAIL_HEADER_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsEmailHeaderEnabled(bool $isEmailHeaderEnabled): FormInterface
    {
        return $this->setData(self::IS_EMAIL_HEADER_ENABLED, $isEmailHeaderEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getIsCustomerNotificationEnabled(): bool
    {
        return (bool)$this->getData(self::IS_CUSTOMER_NOTIFICATION_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsCustomerNotificationEnabled(bool $isCustomerNotificationEnabled): FormInterface
    {
        return $this->setData(self::IS_CUSTOMER_NOTIFICATION_ENABLED, $isCustomerNotificationEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getAdminNotificationEmail(): ?string
    {
        return $this->getData(self::ADMIN_NOTIFICATION_EMAIL)?: $this->scopeConfig->getValue(self::PATH_ADMIN_NOTIFICATION_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setAdminNotificationEmail(?string $adminNotificationEmail): FormInterface
    {
        return $this->setData(self::ADMIN_NOTIFICATION_EMAIL, $adminNotificationEmail);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerNotificationReplyTo(): ?string
    {
        return $this->getData(self::CUSTOMER_NOTIFICATION_REPLY_TO);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerNotificationReplyTo(?string $customerNotificationReplyTo): FormInterface
    {
        return $this->setData(self::CUSTOMER_NOTIFICATION_REPLY_TO, $customerNotificationReplyTo);
    }

    /**
     * @inheritDoc
     */
    public function getAdminNotificationTemplateId(): ?int
    {
        return $this->getData(self::ADMIN_NOTIFICATION_TEMPLATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAdminNotificationTemplateId(?int $adminNotificationTemplateId): FormInterface
    {
        return $this->setData(self::ADMIN_NOTIFICATION_TEMPLATE_ID, $adminNotificationTemplateId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerNotificationTemplateId(): ?int
    {
        return $this->getData(self::CUSTOMER_NOTIFICATION_TEMPLATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerNotificationTemplateId(?int $customerNotificationTemplateId): FormInterface
    {
        return $this->setData(self::CUSTOMER_NOTIFICATION_TEMPLATE_ID, $customerNotificationTemplateId);
    }

    /**
     * @inheritDoc
     */
    public function getEmailReplyTemplateId(): ?int
    {
        return $this->getData(self::EMAIL_REPLY_TEMPLATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEmailReplyTemplateId(?int $emailReplyTemplateId): FormInterface
    {
        return $this->setData(self::EMAIL_REPLY_TEMPLATE_ID, $emailReplyTemplateId);
    }

    /**
     * @inheritDoc
     */
    public function getIsApprovalNotificationEnabled(): bool
    {
        return (bool)$this->getData(self::IS_APPROVAL_NOTIFICATION_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsApprovalNotificationEnabled(bool $isApprovalNotificationEnabled): FormInterface
    {
        return $this->setData(self::IS_APPROVAL_NOTIFICATION_ENABLED, $isApprovalNotificationEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getApprovalNotificationApprovedTemplateId(): int
    {
        return (int)$this->getData(self::APPROVAL_NOTIFICATION_APPROVED_TEMPLATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setApprovalNotificationApprovedTemplateId(int $approvalNotificationApprovedTemplateId): FormInterface
    {
        return $this->setData(self::APPROVAL_NOTIFICATION_APPROVED_TEMPLATE_ID, $approvalNotificationApprovedTemplateId);
    }

    /**
     * @inheritDoc
     */
    public function getApprovalNotificationCompletedTemplateId(): int
    {
        return (int)$this->getData(self::APPROVAL_NOTIFICATION_COMPLETED_TEMPLATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setApprovalNotificationCompletedTemplateId(int $approvalNotificationCompletedTemplateId): FormInterface
    {
        return $this->setData(self::APPROVAL_NOTIFICATION_COMPLETED_TEMPLATE_ID, $approvalNotificationCompletedTemplateId);
    }

    /**
     * @inheritDoc
     */
    public function getApprovalNotificationNotapprovedTemplateId(): int
    {
        return (int)$this->getData(self::APPROVAL_NOTIFICATION_NOTAPPROVED_TEMPLATE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setApprovalNotificationNotapprovedTemplateId(int $approvalNotificationNotapprovedTemplateId): FormInterface
    {
        return $this->setData(self::APPROVAL_NOTIFICATION_NOTAPPROVED_TEMPLATE_ID, $approvalNotificationNotapprovedTemplateId);
    }

    /**
     * @inheritDoc
     */
    public function getIsAdminNotificationAttachmentEnabled(): bool
    {
        return (bool)$this->getData(self::IS_ADMIN_NOTIFICATION_ATTACHMENT_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsAdminNotificationAttachmentEnabled(bool $isAdminNotificationAttachmentEnabled): FormInterface
    {
        return $this->setData(self::IS_ADMIN_NOTIFICATION_ATTACHMENT_ENABLED, $isAdminNotificationAttachmentEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getIsCustomerNotificationAttachmentEnabled(): bool
    {
        return (bool)$this->getData(self::IS_CUSTOMER_NOTIFICATION_ATTACHMENT_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsCustomerNotificationAttachmentEnabled(bool $isCustomerNotificationAttachmentEnabled): FormInterface
    {
        return $this->setData(self::IS_CUSTOMER_NOTIFICATION_ATTACHMENT_ENABLED, $isCustomerNotificationAttachmentEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getIsSurvey(): bool
    {
        return (bool)$this->getData(self::IS_SURVEY);
    }

    /**
     * @inheritDoc
     */
    public function setIsSurvey(bool $survey): FormInterface
    {
        return $this->setData(self::IS_SURVEY, $survey);
    }

    /**
     * @inheritDoc
     */
    public function getIsSuccessSessionDisplayed(): bool
    {
        return (bool)$this->getData(self::IS_SUCCESS_SESSION_DISPLAYED);
    }

    /**
     * @inheritDoc
     */
    public function setIsSuccessSessionDisplayed(bool $isSuccessSessionDisplayed): FormInterface
    {
        return $this->setData(self::IS_SUCCESS_SESSION_DISPLAYED, $isSuccessSessionDisplayed);
    }

    /**
     * @inheritDoc
     */
    public function getIsApprovalControlsEnabled(): bool
    {
        return (bool)$this->getData(self::IS_APPROVAL_CONTROLS_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsApprovalControlsEnabled(bool $isApprovalControlsEnabled): FormInterface
    {
        return $this->setData(self::IS_APPROVAL_CONTROLS_ENABLED, $isApprovalControlsEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getCaptchaMode(): string
    {
        return $this->getData(self::CAPTCHA_MODE) ?? 'default';
    }

    /**
     * @inheritDoc
     */
    public function setCaptchaMode(string $captchaMode): FormInterface
    {
        return $this->setData(self::CAPTCHA_MODE, $captchaMode);
    }

    /**
     * @inheritDoc
     */
    public function getFilesUploadLimit(): ?int
    {
        return $this->getData(self::FILES_UPLOAD_LIMIT);
    }

    /**
     * @inheritDoc
     */
    public function setFilesUploadLimit(?int $filesUploadLimit): FormInterface
    {
        return $this->setData(self::FILES_UPLOAD_LIMIT, $filesUploadLimit);
    }

    /**
     * @inheritDoc
     */
    public function getImagesUploadLimit(): ?int
    {
        return $this->getData(self::IMAGES_UPLOAD_LIMIT);
    }

    /**
     * @inheritDoc
     */
    public function setImagesUploadLimit(?int $imagesUploadLimit): FormInterface
    {
        return $this->setData(self::IMAGES_UPLOAD_LIMIT, $imagesUploadLimit);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): FormInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): FormInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive(bool $isActive): FormInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    public function getIsMenuLinkEnabled(): bool
    {
        return (bool)$this->getData(self::IS_MENU_LINK_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsMenuLinkEnabled(bool $isMenuLinkEnabled): FormInterface
    {
        return $this->setData(self::IS_MENU_LINK_ENABLED, $isMenuLinkEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitButtonText(): ?string
    {
        $submit_button_text = trim((string)$this->getData(self::SUBMIT_BUTTON_TEXT));
        if (strlen($submit_button_text) == 0) {
            $submit_button_text = 'Submit';
        }
        return $submit_button_text;
    }

    /**
     * @inheritDoc
     */
    public function setSubmitButtonText(?string $submitButtonText): FormInterface
    {
        return $this->setData(self::SUBMIT_BUTTON_TEXT, $submitButtonText);
    }


    /**
     * @inheritDoc
     */
    public function getIsCustomerAccessLimited(): bool
    {
        return (bool)$this->getData(self::IS_CUSTOMER_ACCESS_LIMITED);
    }

    /**
     * @inheritDoc
     */
    public function setIsCustomerAccessLimited(bool $isCustomerAccessLimited): FormInterface
    {
        return $this->setData(self::IS_CUSTOMER_ACCESS_LIMITED, $isCustomerAccessLimited);
    }

    /**
     * @inheritDoc
     */
    public function getAccessGroupsSerialized(): ?string
    {
        return $this->getData(self::ACCESS_GROUPS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setAccessGroupsSerialized(?string $accessGroupsSerialized): FormInterface
    {
        return $this->setData(self::ACCESS_GROUPS_SERIALIZED, $accessGroupsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getIsCustomerDashboardEnabled(): bool
    {
        return (bool)$this->getData(self::IS_CUSTOMER_DASHBOARD_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsCustomerDashboardEnabled(bool $isCustomerDashboardEnabled): FormInterface
    {
        return $this->setData(self::IS_CUSTOMER_DASHBOARD_ENABLED, $isCustomerDashboardEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getDashboardGroupsSerialized(): ?string
    {
        return $this->getData(self::DASHBOARD_GROUPS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setDashboardGroupsSerialized(?string $dashboardGroupsSerialized): FormInterface
    {
        return $this->setData(self::DASHBOARD_GROUPS_SERIALIZED, $dashboardGroupsSerialized);
    }


    /**
     * @inheritDoc
     */
    public function getCustomerNotificationSenderName(): string
    {
        return (string)$this->getData(self::CUSTOMER_NOTIFICATION_SENDER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerNotificationSenderName(?string $customerNotificationSenderName): FormInterface
    {
        return $this->setData(self::CUSTOMER_NOTIFICATION_SENDER_NAME, $customerNotificationSenderName);
    }

    /**
     * @inheritDoc
     */
    public function getAdminNotificationBcc(): ?string
    {
        return (string)$this->getData(self::ADMIN_NOTIFICATION_BCC);
    }

    /**
     * @inheritDoc
     */
    public function setAdminNotificationBcc(?string $adminNotificationBcc): FormInterface
    {
        return $this->setData(self::ADMIN_NOTIFICATION_BCC, $adminNotificationBcc);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerNotificationBcc(): ?string
    {
        return $this->getData(self::CUSTOMER_NOTIFICATION_BCC);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerNotificationBcc(?string $customerNotificationBcc): FormInterface
    {
        return $this->setData(self::CUSTOMER_NOTIFICATION_BCC, $customerNotificationBcc);
    }

    /**
     * @inheritDoc
     */
    public function getApprovalNotificationBcc(): string
    {
        return (string)$this->getData(self::APPROVAL_NOTIFICATION_BCC);
    }

    /**
     * @inheritDoc
     */
    public function setApprovalNotificationBcc(?string $approvalNotificationBcc): FormInterface
    {
        return $this->setData(self::APPROVAL_NOTIFICATION_BCC, $approvalNotificationBcc);
    }

    /**
     * @inheritDoc
     */
    public function getIsUrlParametersAccepted(): bool
    {
        return (bool)$this->getData(self::IS_URL_PARAMETERS_ACCEPTED);
    }

    /**
     * @inheritDoc
     */
    public function setIsUrlParametersAccepted(bool $isUrlParametersAccepted): FormInterface
    {
        return $this->setData(self::IS_URL_PARAMETERS_ACCEPTED, $isUrlParametersAccepted);
    }

    /**
     * @inheritDoc
     */
    public function getIsFrontendDownloadAllowed(): bool
    {
        return (bool)$this->getData(self::IS_FRONTEND_DOWNLOAD_ALLOWED);
    }

    /**
     * @inheritDoc
     */
    public function setIsFrontendDownloadAllowed(bool $isFrontendDownloadAllowed): FormInterface
    {
        return $this->setData(self::IS_FRONTEND_DOWNLOAD_ALLOWED, $isFrontendDownloadAllowed);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerResultPermissionsSerialized(): ?string
    {
        return $this->getData(self::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerResultPermissionsSerialized(?string $customerResultPermissionsSerialized): FormInterface
    {
        return $this->setData(self::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED, $customerResultPermissionsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerNotificationAttachmentsSerialized(): ?string
    {
        return $this->getData(self::CUSTOMER_NOTIFICATION_ATTACHMENTS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerNotificationAttachmentsSerialized(?string $customerNotificationAttachmentsSerialized): FormInterface
    {
        return $this->setData(self::CUSTOMER_NOTIFICATION_ATTACHMENTS_SERIALIZED, $customerNotificationAttachmentsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getIsWidthLgShowed(): bool
    {
        return (bool)$this->getData(self::IS_WIDTH_LG_SHOWED);
    }

    /**
     * @inheritDoc
     */
    public function setIsWidthLgShowed(bool $isWidthLgShowed): FormInterface
    {
        return $this->setData(self::IS_WIDTH_LG_SHOWED, $isWidthLgShowed);
    }

    /**
     * @inheritDoc
     */
    public function getIsWidthMdShowed(): bool
    {
        return (bool)$this->getData(self::IS_WIDTH_MD_SHOWED);
    }

    /**
     * @inheritDoc
     */
    public function setIsWidthMdShowed(bool $isWidthMdShowed): FormInterface
    {
        return $this->setData(self::IS_WIDTH_MD_SHOWED, $isWidthMdShowed);
    }

    /**
     * @inheritDoc
     */
    public function getIsWidthSmShowed(): bool
    {
        return (bool)$this->getData(self::IS_WIDTH_SM_SHOWED);
    }

    /**
     * @inheritDoc
     */
    public function setIsWidthSmShowed(bool $isWidthSmShowed): FormInterface
    {
        return $this->setData(self::IS_WIDTH_SM_SHOWED, $isWidthSmShowed);
    }

    /**
     * @inheritDoc
     */
    public function getTemplate(): string
    {
        return empty($this->getData(self::TEMPLATE)) ? Template::DEFAULT : $this->getData(self::TEMPLATE);
    }

    /**
     * @inheritDoc
     */
    public function setTemplate(string $template): FormInterface
    {
        return $this->setData(self::TEMPLATE, $template);
    }

    /**
     * @inheritDoc
     */
    public function getCssClass(): ?string
    {
        return $this->getData(self::CSS_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setCssClass(?string $cssClass): FormInterface
    {
        return $this->setData(self::CSS_CLASS, $cssClass);
    }

    /**
     * @inheritDoc
     */
    public function getCssStyle(): ?string
    {
        return $this->getData(self::CSS_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setCssStyle(?string $cssStyle): FormInterface
    {
        return $this->setData(self::CSS_STYLE, $cssStyle);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedAfterSubmission(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_AFTER_SUBMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedAfterSubmission(bool $isDisplayed): FormInterface
    {
        return $this->setData(self::IS_DISPLAYED_AFTER_SUBMISSION, $isDisplayed);
    }

    /**
     * @inheritDoc
     */
    public function getIsScrolledAfterSubmission(): bool
    {
        return (bool)$this->getData(self::IS_SCROLLED_AFTER_SUBMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setIsScrolledAfterSubmission(bool $isScrolled): FormInterface
    {
        return $this->setData(self::IS_SCROLLED_AFTER_SUBMISSION, $isScrolled);
    }

    /**
     * @inheritDoc
     */
    public function getIsAsyncLoaded(): bool
    {
        return (bool)$this->getData(self::IS_ASYNC_LOADED);
    }

    /**
     * @inheritDoc
     */
    public function setIsAsyncLoaded(bool $isAsyncLoaded): FormInterface
    {
        return $this->setData(self::IS_ASYNC_LOADED, $isAsyncLoaded);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitButtonPosition(): ?string
    {
        return $this->getData(self::SUBMIT_BUTTON_POSITION);
    }

    /**
     * @inheritDoc
     */
    public function setSubmitButtonPosition(?string $submitButtonPosition): FormInterface
    {
        return $this->setData(self::SUBMIT_BUTTON_POSITION, $submitButtonPosition);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitButtonSize(): ?string
    {
        return $this->getData(self::SUBMIT_BUTTON_SIZE);
    }

    /**
     * @inheritDoc
     */
    public function setSubmitButtonSize(?string $submitButtonSize): FormInterface
    {
        return $this->setData(self::SUBMIT_BUTTON_SIZE, $submitButtonSize);
    }

    /**
     * @inheritDoc
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setUrlKey(?string $urlKey): FormInterface
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @inheritDoc
     */
    public function getMetaKeywords(): ?string
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @inheritDoc
     */
    public function setMetaKeywords(?string $metaKeywords): FormInterface
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * @inheritDoc
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setMetaDescription(?string $metaDescription): FormInterface
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @inheritDoc
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setMetaTitle(?string $metaTitle): FormInterface
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @inheritDoc
     */
    public function getBelowText(): ?string
    {
        return $this->getData(self::BELOW_TEXT);
    }

    /**
     * @inheritDoc
     */
    public function setBelowText(?string $belowText): FormInterface
    {
        return $this->setData(self::BELOW_TEXT, $belowText);
    }

    /**
     * @inheritDoc
     */
    public function getOnLoadScript(): ?string
    {
        return $this->getData(self::ON_LOAD_SCRIPT);
    }

    /**
     * @inheritDoc
     */
    public function setOnLoadScript(?string $script): FormInterface
    {
        return $this->setData(self::ON_LOAD_SCRIPT, $script);
    }

    /**
     * @inheritDoc
     */
    public function getAfterSubmissionScript(): ?string
    {
        return $this->getData(self::AFTER_SUBMISSION_SCRIPT);
    }

    /**
     * @inheritDoc
     */
    public function setAfterSubmissionScript(?string $script): FormInterface
    {
        return $this->setData(self::AFTER_SUBMISSION_SCRIPT, $script);
    }

    /**
     * @inheritDoc
     */
    public function getAdminNotificationSenderName(): string
    {
        return (string)$this->getData(self::ADMIN_NOTIFICATION_SENDER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setAdminNotificationSenderName(?string $adminNotificationSenderName): FormInterface
    {
        return $this->setData(self::ADMIN_NOTIFICATION_SENDER_NAME, $adminNotificationSenderName);
    }

    /**
     * @inheritDoc
     */
    public function getAdminNotificationSenderEmail(): ?string
    {
        return $this->getData(self::ADMIN_NOTIFICATION_SENDER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setAdminNotificationSenderEmail(?string $adminNotificationSenderEmail): FormInterface
    {
        return $this->setData(self::ADMIN_NOTIFICATION_SENDER_EMAIL, $adminNotificationSenderEmail);
    }

#endregion

    /**
     * @inheritDoc
     */
    public function getDashboardGroups(): array
    {
        $groups = $this->getData(self::DASHBOARD_GROUPS);
        return is_array($groups) ? $groups : [];
    }

    /**
     * @inheritDoc
     */
    public function setDashboardGroups(array $dashboardGroups): FormInterface
    {
        return $this->setData(self::DASHBOARD_GROUPS, $dashboardGroups);
    }

    /**
     * @inheritDoc
     */
    public function getAccessGroups(): array
    {
        $groups = $this->getData(self::ACCESS_GROUPS);
        return is_array($groups) ? $groups : [];
    }

    /**
     * @inheritDoc
     */
    public function setAccessGroups(array $accessGroups): FormInterface
    {
        return $this->setData(self::ACCESS_GROUPS, $accessGroups);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerResultPermissions(): array
    {
        $permissions = $this->getData(self::CUSTOMER_RESULT_PERMISSIONS);
        return is_array($permissions) ? $permissions : [];
    }

    /**
     * @inheritDoc
     */
    public function setCustomerResultPermissions(array $customerResultPermissions)
    {
        return $this->setData(self::CUSTOMER_RESULT_PERMISSIONS, $customerResultPermissions);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerNotificationAttachments(): array
    {
        $value = $this->getData(self::CUSTOMER_NOTIFICATION_ATTACHMENTS);
        return is_array($value) ? $value : [];
    }

    /**
     * @inheritDoc
     */
    public function setCustomerNotificationAttachments(array $customerNotificationAttachments)
    {
        return $this->setData(self::CUSTOMER_NOTIFICATION_ATTACHMENTS, $customerNotificationAttachments);
    }

    /**
     * @inheritDoc
     */
    public function getIsSubmissionNotStored(): bool
    {
        return (bool)$this->getData(self::IS_SUBMISSIONS_NOT_STORED);
    }

    /**
     * @inheritDoc
     */
    public function setIsSubmissionNotStored(bool $isSubmissionNotStored): FormInterface
    {
        return $this->setData(self::IS_SUBMISSIONS_NOT_STORED, $isSubmissionNotStored);
    }

    /**
     * @inheritDoc
     */
    public function getIsCleanupEnabled(): bool
    {
        return (bool)$this->getData(self::IS_CLEANUP_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setIsCleanupEnabled(bool $isCleanupEnabled): FormInterface
    {
        return $this->setData(self::IS_CLEANUP_ENABLED, $isCleanupEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getCleanupPeriod(): ?int
    {
        return $this->getData(self::CLEANUP_PERIOD);
    }

    /**
     * @inheritDoc
     */
    public function setCleanupPeriod(int $cleanupPeriod): FormInterface
    {
        return $this->setData(self::CLEANUP_PERIOD, $cleanupPeriod);
    }

    public function getUnreadCount(): int
    {
        return $this->getData(self::UNREAD_COUNT);
    }

    public function setUnreadCount(int $unreadCount): FormInterface
    {
        return $this->setData(self::UNREAD_COUNT, $unreadCount);
    }

    /**
     * Get this fields logic
     *
     * @param bool $all
     * @return LogicInterface[]|Logic[]
     */
    public function getLogic(bool $all = true): array
    {
        $result = [];
        if (!$this->getId()) return $result;

        /** @var LogicInterface[] $rules */
        $rules = $this->logicRepository->getListByFormId($this->getId(), $all, $this->getStoreId())->getItems();
        foreach ($rules as $rule) {
            try {
                $field = $this->fieldRepository->getById($rule->getFieldId());
                if ($field->getIsLogicType()) {
                    $result[] = $rule;
                }
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $result;
    }

    /**
     * Update fieldsets position
     *
     * @throws LocalizedException
     */
    public function updateFieldsetPositions()
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(FieldsetInterface::POSITION)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldsetInterface::FORM_ID, $this->getId())
            ->addSortOrder($sortOrder)
            ->create();

        /** @var FieldsetInterface[] $fieldsets */
        $fieldsets      = $this->fieldsetRepository->getList($searchCriteria)->getItems();
        $i              = 1;

        foreach ($fieldsets as $fieldset) {
            $fieldset->setPosition($i * 10);
            $this->fieldsetRepository->save($fieldset);
            $i++;
        }
    }

    /**
     * Update fields position
     *
     * @throws LocalizedException
     */
    public function updateFieldPositions()
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(FieldInterface::POSITION)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $this->getId())
            ->addSortOrder($sortOrder)
            ->create();

        /** @var FieldInterface[] $fields */
        $fields         = $this->fieldRepository->getList($searchCriteria)->getItems();
        $i              = 1;

        foreach ($fields as $field) {
            $field->setPosition($i * 10);
            $this->fieldRepository->save($field);
            $i++;
        }
    }

}
