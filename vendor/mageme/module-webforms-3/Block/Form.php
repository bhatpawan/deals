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

namespace MageMe\WebForms\Block;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Config\Options\Form\Template as TemplateOptions;
use MageMe\WebForms\Config\Options\Status;
use MageMe\WebForms\Controller\Form\Load;
use MageMe\WebForms\Controller\Form\Preview;
use MageMe\WebForms\Helper\CaptchaHelper as Captcha;
use MageMe\WebForms\Helper\CaptchaHelperFactory as CaptchaFactory;
use MageMe\WebForms\Helper\SurveyHelper;
use MageMe\WebForms\Helper\TranslationHelper;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory as ResultCollectionFactory;
use MageMe\WebForms\Model\Result;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Form
 * @package MageMe\WebForms\Block
 */
class Form extends Template
{
    /**
     *
     */
    const FORM_SUCCESS = 'form_success';
    /**
     *
     */
    const ASYNC_TEMPLATE = 'form/async.phtml';
    /**
     *
     */
    const ACCESS_DENIED_TEMPLATE = 'access_denied.phtml';
    /**
     *
     */
    const OVERRIDDEN_TEMPLATE = 'overridden_template';

    /**
     * @var FormInterface|\MageMe\WebForms\Model\Form
     */
    protected $form;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FieldCollectionFactory
     */
    protected $fieldCollectionFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var bool
     */
    protected $success = false;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var ResultCollectionFactory
     */
    protected $resultCollectionFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Http
     */
    protected $response;

    /**
     * @var CaptchaFactory
     */
    protected $captcha;

    /**
     * @var string
     */
    protected $_uid;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var SurveyHelper
     */
    protected $surveyHelper;
    /**
     * @var TemplateOptions
     */
    protected $templateOptions;
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var Form\Element\Form
     */
    protected $formBlock;

    /**
     * @var Form\Element\Script
     */
    protected $scriptBlock;

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    /**
     * Form constructor.
     * @param Form\Context $context
     */
    public function __construct(
        Form\Context $context
    )
    {
        parent::__construct($context->getContext(), $context->getData());

        $this->customerUrl             = $context->getCustomerUrl();
        $this->filterProvider          = $context->getFilterProvider();
        $this->coreRegistry            = $context->getCoreRegistry();
        $this->formFactory             = $context->getFormFactory();
        $this->customerSessionFactory  = $context->getCustomerSessionFactory();
        $this->session                 = $this->customerSessionFactory->create();
        $this->resultCollectionFactory = $context->getResultCollectionFactory();
        $this->resultFactory           = $context->getResultFactory();
        $this->response                = $context->getResponse();
        $this->captcha                 = $context->getCaptcha();
        $this->random                  = $context->getRandom();
        $this->fieldCollectionFactory  = $context->getFieldCollectionFactory();
        $this->surveyHelper            = $context->getSurveyHelper();
        $this->templateOptions         = $context->getTemplateOptions();
        $this->formRepository          = $context->getFormRepository();
        $this->formBlock               = $context->getFormBlock();
        $this->scriptBlock             = $context->getScriptBlock();
        $this->translationHelper       = $context->getTranslationHelper();
    }

    /**
     * @return Form\Element\Script
     */
    public function getScriptBlock(): Form\Element\Script
    {
        return $this->scriptBlock->setUid($this->_uid)->setForm($this->getForm());
    }

    /**
     * @return bool
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $value
     */
    public function setSuccess(bool $value)
    {
        $this->success = $value;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->coreRegistry;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return bool|Captcha
     */
    public function getCaptcha()
    {
        return $this->getForm()->getCaptcha();
    }

    /**
     * @return \MageMe\WebForms\Model\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setForm($form): Form
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUid(): string
    {
        if ($this->_scopeConfig->getValue('webforms/general/use_uid')) {
            if (!$this->_uid) {
                $this->_uid = $this->random->getRandomString(6);
            }
            return $this->_uid;
        }
        return $this->getForm()->getId();
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->applyTranslation($this->getData(FormInterface::DESCRIPTION));
    }

    /**
     * @param string|null $str
     * @return string
     */
    public function applyTranslation(?string $str): ?string
    {
        return $this->translationHelper->applyTranslation($str);
    }

    /**
     * @return null|string
     */
    public function getSuccessText(): ?string
    {
        return $this->applyTranslation($this->getData(FormInterface::SUCCESS_TEXT));
    }

    /**
     * @return Form\Element\Form
     */
    public function getFormBlock(): Form\Element\Form
    {
        return $this->formBlock->setUid($this->_uid)->setForm($this->getForm())->setResult($this->result)->setTemplate('form/element/form.phtml');
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setCustomerSessionData();

        return $this;
    }

    /**
     * @return $this
     */
    protected function setCustomerSessionData(): Form
    {
        if ($this->coreRegistry->registry('current_category')) {
            $this->session->setData('last_viewed_category_id',
                $this->coreRegistry->registry('current_category')->getId());
        }

        if ($this->coreRegistry->registry('current_product')) {
            $this->session->setData('last_viewed_product_id',
                $this->coreRegistry->registry('current_product')->getId());
        }

        if (!$this->coreRegistry->registry('current_url')) {
            try {
                $this->coreRegistry->register('current_url', $this->_storeManager->getStore()->getCurrentUrl(false));
            } catch (NoSuchEntityException $e) {
            }
        }
        $this->session->setData('last_viewed_url', $this->coreRegistry->registry('current_url'));
        $this->setData('current_url', $this->coreRegistry->registry('current_url'));

        return $this;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _toHtml()
    {
        try {
            $this->initForm();
        } catch (NoSuchEntityException $e) {
            return $this->formNotInitializedAction($e->getMessage());
        }

        if ($this->getForm()->getIsActive() == Status::STATUS_DISABLED || !$this->isDirectAvailable()) {
            return $this->getNotAvailableMessage();
        }

        $html = parent::_toHtml();

        $messages = $this->getMessages();

        return $messages->getGroupedHtml() . $html;
    }

    /**
     * @return $this|Http|HttpInterface
     * @throws NoSuchEntityException|LocalizedException
     */
    protected function initForm()
    {
        $show_success = false;
        $data         = $this->getFormData();
        $storeId      = $this->_storeManager->getStore()->getId();

        if ($this->getForm()) {
            $form = $this->getForm();
        } else {
            $form = $this->getData('webform_code') ? $this->formRepository->getByCode($this->getData('webform_code')) :
                $this->formRepository->getById($data[FormInterface::ID], $storeId);
            $this->setForm($form);
        }

        $result = ($this->getResult() && $this->getResult()->getId()) ? $this->getResult() : $this->getResultFromUrl();
        $this->formBlock->setResult($result);
        $form->getFieldsToFieldsets(false, $result);

        //delete form temporary data
        if ($this->isAjax()) {
            $this->_session->setData('webform_result_tmp_' . $form->getId(), false);
        }

        //process texts
        if ($form->getDescription()) {
            $this->setDescription($this->getPageFilter($form)->filter($form->getDescription()));
        }
        if ($form->getSuccessText()) {
            $this->setSuccessText($this->getPageFilter($form)->filter($form->getSuccessText()));
        }

        $this->session = $this->customerSessionFactory->create();
        $loggedIn      = $this->session->isLoggedIn();
        if ($form->getIsSurvey()) {
            if ($loggedIn) {
                $collection = $this->resultCollectionFactory->create()->addFilter(ResultInterface::FORM_ID, $data[FormInterface::ID]);
                $collection->addFilter(ResultInterface::CUSTOMER_ID, $this->session->getCustomerId());
                $show_success = $collection->count() > 0;
            } else {
                $show_success = (bool)$this->surveyHelper->getCookie($form->getId());
            }
        }

        if ($this->session->getData(self::FORM_SUCCESS) == $this->getForm()->getId() || $show_success) {
            $this->setSuccess(true);
            $this->session->setData(self::FORM_SUCCESS, null);
            if ($this->session->getData('webform_result_' . $form->getId()) && !$form->getIsSubmissionNotStored()) {

                // apply custom variables
                $resultObject = $this->resultFactory->create()->load(($this->session->getData('webform_result_' . $form->getId())));
                $filter     = $this->getPageFilter($form, $resultObject);
                if ($form->getDescription()) {
                    $this->setDescription($filter->filter($form->getDescription()));
                }
                if ($form->getSuccessText()) {
                    $this->setSuccessText($filter->filter($form->getSuccessText()));
                }
                $this->setAfterSubmissionScript($filter->filter($form->getAfterSubmissionScript()));
            }
        }
        if ($form->getIsCustomerAccessLimited() && !$loggedIn && !$this->getData('results')) {
            $this->session->setBeforeAuthUrl($this->_urlBuilder->getCurrentUrl());
            $login_url = $this->customerUrl->getLoginUrl();
            $status    = 301;

            if ($this->_storeManager->getStore()->getConfig('webforms/general/login_redirect')) {
                $login_url = $this->getUrl($this->_storeManager->getStore()->getConfig('webforms/general/login_redirect'));

                if (strstr((string)$this->_storeManager->getStore()->getConfig('webforms/general/login_redirect'), '://')) {
                    $login_url = $this->_storeManager->getStore()->getConfig('webforms/general/login_redirect');
                }

            }
            $this->setTemplate(self::ACCESS_DENIED_TEMPLATE);
            return $this->response->setRedirect($login_url, $status);
        }

        // Set template
        $this->setTemplate($this->getData(self::OVERRIDDEN_TEMPLATE) ?? $this->getForm()->getTemplate());

        $this->compatibilityPatch();

        if ($this->getForm()->getIsAsyncLoaded() && !$this->isLoaded()) {
            $this->switchToAsyncTemplate();
        }

        if (!$this->getForm()->canAccess()) {
            $this->setTemplate(self::ACCESS_DENIED_TEMPLATE);
        }


        return $this;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFormData(): array
    {
        $data = $this->getRequest()->getParams();

        if ($this->getData(FormInterface::ID)) {
            $data[FormInterface::ID] = $this->getData(FormInterface::ID);
        }

        if (empty($data[FormInterface::ID])) {
            $data[FormInterface::ID] = $this->_storeManager->getStore()->getConfig('webforms/contacts/webform');
        }
        return $data;
    }

    /**
     * @return Result
     */
    public function getResult(): Result
    {
        if ($this->result) {
            return $this->result;
        }
        return $this->resultFactory->create();
    }

    /**
     * @param Result $result
     * @return $this
     */
    public function setResult(Result $result): Form
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return Result
     */
    public function getResultFromUrl(): Result
    {
        $result = $this->resultFactory->create()->setId(true);
        $data   = [];
        if ($this->getForm()) {
            if ($this->getForm()->getIsUrlParametersAccepted()) {
                $urlParams = $this->getRequest()->getParams();
                foreach ($urlParams as $fieldCode => $value) {
                    if ($fieldCode) {
                        $fieldCollection = $this->fieldCollectionFactory->create()
                            ->addFilter(FieldInterface::FORM_ID, $this->getForm()->getId())
                            ->addFilter(FieldInterface::CODE, $fieldCode);
                        if (count($fieldCollection)) {
                            $field = $fieldCollection->getFirstItem();
                            if ($field->getId()) {
                                $data[$field->getId()] = $value;
                                $result->setData('field_' . $field->getId(), $value);
                            }
                        }
                    }
                }
            }
        }

        $result->setData('field', $data);

        return $result;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function isAjax(): ?string
    {
        return $this->_storeManager->getStore()->getConfig('webforms/general/ajax');
    }

    /**
     * @param string|null $description
     * @return Form
     */
    public function setDescription(?string $description): Form
    {
        return $this->setData(FormInterface::DESCRIPTION, $description);
    }

    /**
     * @param null|FormInterface $form
     * @param null|ResultInterface $result
     * @return \Magento\Framework\Filter\Template
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getPageFilter($form = null, $result = null): \Magento\Framework\Filter\Template
    {
        $filter = $this->filterProvider->getPageFilter();
        if ($form instanceof FormInterface) {
            $filter->setVariables([
                'webform_name' => $form->getName(),
                'webform' => new DataObject($form->getData()),
            ]);
        }
        if ($result instanceof ResultInterface) {
            $filter->setVariables([
                'webform_result' => $result->toHtml(),
                'result' => $result->getTemplateResultVar(),
                'webform_subject' => $result->getSubject(),
            ]);
        }
        return $filter;
    }

    /**
     * @param string|null $successText
     * @return Form
     */
    public function setSuccessText(?string $successText): Form
    {
        return $this->setData(FormInterface::SUCCESS_TEXT, $successText);
    }

    /**
     * Compatibility patch
     */
    protected function compatibilityPatch(): Form
    {
        $template = str_replace('webforms/', '', $this->getTemplate(), $replaced);
        $changed  = (bool)$replaced;
        $template = str_replace('VladimirPopov', 'MageMe', $template, $replaced);
        $changed  = $changed || $replaced;
        if ($changed) {
            $this->setTemplate($template);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return (bool)$this->getData(Load::FORM_LOADED);
    }

    /**
     * Switch to async template
     */
    protected function switchToAsyncTemplate()
    {
        $this->setData('widget_template', $this->getTemplate());
        $this->setData('async_url',
            $this->getUrl('webforms/form/load', ['_current' => true]));
        $this->setTemplate(self::ASYNC_TEMPLATE);
    }

    /**
     * @param string $message
     * @return string
     * @throws LocalizedException
     */
    protected function formNotInitializedAction(string $message): string
    {
        $messages = $this->getMessages();
        $messages->addError(__('Error on form initializing: %1', $message));
        return $messages->getGroupedHtml();
    }

    /**
     * @return Messages
     * @throws LocalizedException
     */
    protected function getMessages(): Messages
    {
        $messages = $this->getLayout()->getMessagesBlock();

        if ($this->isLoaded()) {
            return $messages;
        }

        if ($this->_scopeConfig->getValue('webforms/license/active')) {
            if (!$this->_scopeConfig->getValue('webforms/license/active', ScopeInterface::SCOPE_STORE)) {
                $messages->addNotice(__('Please activate WebForms license.'));
            }
        } else {
            $messages->addError($this->_scopeConfig->getValue('webforms/license/serial') ? __('Incorrect serial number.') :
                __('License serial number for WebForms is missing.'));
        }

        return $messages;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isDirectAvailable(): bool
    {
        if ($this->coreRegistry->registry(Preview::IS_FORM_PREVIEW)
            && !$this->_storeManager->getStore()->getConfig('webforms/general/preview_enabled')) {
            return false;
        }
        return true;
    }

    /**
     * @return Phrase
     * @throws NoSuchEntityException
     */
    public function getNotAvailableMessage(): Phrase
    {
        $message = __('Web-form is not active.');

        if ($this->getForm()->getIsActive() && !$this->isDirectAvailable()) {
            $message = __('Web-form is locked by configuration and can not be accessed directly.');
        }

        return $message;
    }

    /**
     * @return string
     */
    public function getAfterSubmissionScript(): string
    {
        return (string)$this->getData(FormInterface::AFTER_SUBMISSION_SCRIPT);
    }

    /**
     * @param string|null $script
     * @return Form
     */
    public function setAfterSubmissionScript(?string $script) {
        return $this->setData(FormInterface::AFTER_SUBMISSION_SCRIPT, $script);
    }
}
