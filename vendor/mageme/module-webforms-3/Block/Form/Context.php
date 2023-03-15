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

namespace MageMe\WebForms\Block\Form;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Block\Form\Element\Form;
use MageMe\WebForms\Block\Form\Element\Script;
use MageMe\WebForms\Config\Options\Form\Template as TemplateOptions;
use MageMe\WebForms\Helper\CaptchaHelperFactory as CaptchaFactory;
use MageMe\WebForms\Helper\SurveyHelper;
use MageMe\WebForms\Helper\TranslationHelper;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory as ResultCollectionFactory;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 *
 */
class Context
{
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
     * @var Random
     */
    protected $random;

    /**
     * @var
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
     * @var Form
     */
    protected $formBlock;

    /**
     * @var Template\Context
     */
    protected $context;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Script
     */
    protected $scriptBlock;

    /**
     * @var TranslationHelper
     */
    protected $translationHelper;

    /**
     * @param TranslationHelper $translationHelper
     * @param FormRepositoryInterface $formRepository
     * @param TemplateOptions $templateOptions
     * @param SurveyHelper $surveyHelper
     * @param SessionFactory $customerSessionFactory
     * @param Template\Context $context
     * @param FilterProvider $filterProvider
     * @param Registry $coreRegistry
     * @param FormFactory $formFactory
     * @param Url $customerUrl
     * @param ResultCollectionFactory $resultCollectionFactory
     * @param ResultFactory $resultFactory
     * @param Http $response
     * @param CaptchaFactory $captcha
     * @param Random $random
     * @param FieldCollectionFactory $fieldCollectionFactory
     * @param Form $formBlock
     * @param Script $scriptBlock
     * @param array $data
     */
    public function __construct(
        TranslationHelper       $translationHelper,
        FormRepositoryInterface $formRepository,
        TemplateOptions         $templateOptions,
        SurveyHelper            $surveyHelper,
        SessionFactory          $customerSessionFactory,
        Template\Context        $context,
        FilterProvider          $filterProvider,
        Registry                $coreRegistry,
        FormFactory             $formFactory,
        Url                     $customerUrl,
        ResultCollectionFactory $resultCollectionFactory,
        ResultFactory           $resultFactory,
        Http                    $response,
        CaptchaFactory          $captcha,
        Random                  $random,
        FieldCollectionFactory  $fieldCollectionFactory,
        Form                    $formBlock,
        Script                  $scriptBlock,
        array                   $data = []
    )
    {
        $this->context                 = $context;
        $this->customerUrl             = $customerUrl;
        $this->filterProvider          = $filterProvider;
        $this->coreRegistry            = $coreRegistry;
        $this->formFactory             = $formFactory;
        $this->customerSessionFactory  = $customerSessionFactory;
        $this->session                 = $customerSessionFactory->create();
        $this->resultCollectionFactory = $resultCollectionFactory;
        $this->resultFactory           = $resultFactory;
        $this->response                = $response;
        $this->captcha                 = $captcha;
        $this->random                  = $random;
        $this->fieldCollectionFactory  = $fieldCollectionFactory;
        $this->surveyHelper            = $surveyHelper;
        $this->templateOptions         = $templateOptions;
        $this->formRepository          = $formRepository;
        $this->formBlock               = $formBlock;
        $this->data                    = $data;
        $this->scriptBlock             = $scriptBlock;
        $this->translationHelper       = $translationHelper;
    }

    /**
     * @return Script
     */
    public function getScriptBlock(): Script
    {
        return $this->scriptBlock;
    }

    /**
     * @return FormInterface|\MageMe\WebForms\Model\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return FilterProvider
     */
    public function getFilterProvider(): FilterProvider
    {
        return $this->filterProvider;
    }

    /**
     * @return Registry
     */
    public function getCoreRegistry(): Registry
    {
        return $this->coreRegistry;
    }

    /**
     * @return FormFactory
     */
    public function getFormFactory(): FormFactory
    {
        return $this->formFactory;
    }

    /**
     * @return FieldCollectionFactory
     */
    public function getFieldCollectionFactory(): FieldCollectionFactory
    {
        return $this->fieldCollectionFactory;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return SessionFactory
     */
    public function getCustomerSessionFactory(): SessionFactory
    {
        return $this->customerSessionFactory;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return Url
     */
    public function getCustomerUrl(): Url
    {
        return $this->customerUrl;
    }

    /**
     * @return ResultCollectionFactory
     */
    public function getResultCollectionFactory(): ResultCollectionFactory
    {
        return $this->resultCollectionFactory;
    }

    /**
     * @return ResultFactory
     */
    public function getResultFactory(): ResultFactory
    {
        return $this->resultFactory;
    }

    /**
     * @return Http
     */
    public function getResponse(): Http
    {
        return $this->response;
    }

    /**
     * @return CaptchaFactory
     */
    public function getCaptcha(): CaptchaFactory
    {
        return $this->captcha;
    }

    /**
     * @return Random
     */
    public function getRandom(): Random
    {
        return $this->random;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return SurveyHelper
     */
    public function getSurveyHelper(): SurveyHelper
    {
        return $this->surveyHelper;
    }

    /**
     * @return TemplateOptions
     */
    public function getTemplateOptions(): TemplateOptions
    {
        return $this->templateOptions;
    }

    /**
     * @return FormRepositoryInterface
     */
    public function getFormRepository(): FormRepositoryInterface
    {
        return $this->formRepository;
    }

    /**
     * @return Form
     */
    public function getFormBlock(): Form
    {
        return $this->formBlock;
    }

    /**
     * @return Template\Context
     */
    public function getContext(): Template\Context
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return TranslationHelper
     */
    public function getTranslationHelper(): TranslationHelper
    {
        return $this->translationHelper;
    }
}