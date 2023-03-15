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

namespace MageMe\WebForms\Model;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Block\Result\Info;
use MageMe\WebForms\Block\Result\View;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Model\Field\Type\Email;
use MageMe\WebForms\Model\Field\Type\SelectContact;
use MageMe\WebForms\Model\Result\AbstractResult;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class Result extends AbstractResult
{
    /**
     * @var FormInterface|null
     */
    protected $form = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_result';

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var TemplateResource
     */
    protected $templateResource;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var RegionResource
     */
    protected $regionResource;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * @var ApprovalStatus
     */
    protected $approvalStatus;

    /**
     * @var View
     */
    protected $viewBlock;

    /**
     * @var Info
     */
    protected $infoBlock;

    /**
     * Result constructor.
     * @param View $viewBlock
     * @param Info $infoBlock
     * @param ApprovalStatus $approvalStatus
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param FormRepositoryInterface $formRepository
     * @param CustomerRegistry $customerRegistry
     * @param RegionResource $regionResource
     * @param RegionFactory $regionFactory
     * @param TemplateResource $templateResource
     * @param TemplateFactory $templateFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TimezoneInterface $timezone
     * @param SessionFactory $sessionFactory
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param Random $random
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        View                            $viewBlock,
        Info                            $infoBlock,
        ApprovalStatus                  $approvalStatus,
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        FieldRepositoryInterface        $fieldRepository,
        FormRepositoryInterface         $formRepository,
        CustomerRegistry                $customerRegistry,
        RegionResource                  $regionResource,
        RegionFactory                   $regionFactory,
        TemplateResource                $templateResource,
        TemplateFactory                 $templateFactory,
        SearchCriteriaBuilder           $searchCriteriaBuilder,
        TimezoneInterface               $timezone,
        SessionFactory                  $sessionFactory,
        RequestInterface                $request,
        ScopeConfigInterface            $scopeConfig,
        StoreManagerInterface           $storeManager,
        DirectoryList                   $directoryList,
        Random                          $random,
        Context                         $context,
        Registry                        $registry,
        ResourceModel\AbstractResource  $resource = null,
        AbstractDb                      $resourceCollection = null,
        array                           $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->random                 = $random;
        $this->directoryList          = $directoryList;
        $this->storeManager           = $storeManager;
        $this->scopeConfig            = $scopeConfig;
        $this->request                = $request;
        $this->customerSession        = $sessionFactory->create();
        $this->timezone               = $timezone;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->templateFactory        = $templateFactory;
        $this->templateResource       = $templateResource;
        $this->regionFactory          = $regionFactory;
        $this->regionResource         = $regionResource;
        $this->customerRegistry       = $customerRegistry;
        $this->formRepository         = $formRepository;
        $this->fieldRepository        = $fieldRepository;
        $this->fileDropzoneRepository = $fileDropzoneRepository;
        $this->approvalStatus         = $approvalStatus;
        $this->viewBlock              = $viewBlock;
        $this->infoBlock              = $infoBlock;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @noinspection DuplicatedCode
     */
    public function getTemplateVars()
    {
        $webformObject = new DataObject();
        $form          = $this->getForm();
        $webformObject->setData($this->convertPhrase($form->getData()));
        $store_group    = $this->storeManager->getStore($this->getStoreId())->getFrontendName();
        $store_name     = $this->storeManager->getStore($this->getStoreId())->getName();
        $customer_email = $this->getCustomerEmail();
        if (isset($customer_email[0])) {
            $customer_email = $customer_email[0];
        }
        $vars     = [
            'webform_name' => $form->getName(),
            'webform_result' => $this->toHtml(),
            'result' => $this->getTemplateResultVar(),
            'customer_name' => $this->getCustomerName(),
            'customer_email' => $customer_email,
            'ip' => $this->getCustomerIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'webform' => $webformObject
        ];
        $customer = $this->getCustomer();
        if ($customer) {
            $vars['customer'] = $customer;
            $billing_address  = $customer->getDefaultBilling();
            if ($billing_address) {
                $vars['billing_address'] = $billing_address;
            }
            $shipping_address = $customer->getDefaultShipping();
            if ($shipping_address) {
                $vars['shipping_address'] = $shipping_address;
            }
        }
        return $this->convertPhrase($vars);
    }

    /**
     * @return FormInterface|Form
     * @throws NoSuchEntityException
     */
    public function getForm()
    {
        if (!$this->form) {
            if ($this->getFormId())
                $this->form = $this->formRepository->getById($this->getFormId(), $this->getStoreId());;
        }
        return $this->form;
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm(FormInterface $form): Result
    {
        if ($form->getId() == $this->getFormId()) {
            $this->form = $form;
        }
        return $this;
    }

    /**
     * @param $object
     * @return array|DataObject|mixed|null
     */
    protected function convertPhrase($object)
    {
        $arr = $object instanceof DataObject ? $object->getData() : $object;
        foreach ($arr as &$item) {
            if ($item instanceof Phrase) {
                $item = $item->render();
            }
        }
        if ($object instanceof DataObject) {
            $object->setData($arr);
        } else {
            return $arr;
        }
        return $object;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerEmail(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $this->getFormId())
            ->addFilter(FieldInterface::TYPE, 'email')
            ->create();
        $fields         = $this->fieldRepository->getList($searchCriteria, $this->getStoreId())->getItems();

        $customerEmail = [];
        foreach ($this->getFieldArray() as $key => $value) {
            $email = is_string($value) ? trim($value) : '';
            /** @var Email $field */
            foreach ($fields as $field) {
                if (strlen($email) > 0 && $key == $field->getId() && !in_array($email, $customerEmail)) {
                    $customerEmail [] = $value;
                }
            }
        }

        if (!count($customerEmail)) {

            // try to get email by customer id
            if ($this->getCustomerId()) {
                $customerEmail [] = $this->customerRegistry->retrieve($this->getCustomerId())->getEmail();
            }
        }

        if (!count($customerEmail)) {
            if ($this->customerSession->isLoggedIn()) {
                $customerEmail [] = $this->customerSession->getCustomer()->getEmail();
            }
        }

        if (!count($customerEmail)) {

            // try to get $_POST['email'] variable
            if ($this->request->getParam('email')) {
                $customerEmail [] = $this->request->getParam('email');
            }
        }

        return array_map('trim', $customerEmail);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getFieldArray(): array
    {
        if (!$this->getData('field')) {
            $this->addFieldArray();
        }
        return is_array($this->getData('field')) ? $this->getData('field') : [];
    }

    /**
     * TODO: comment
     *
     * @param bool $preserveFrontend
     * @return $this
     * @throws LocalizedException
     */
    public function addFieldArray(bool $preserveFrontend = false): Result
    {
        $data       = $this->getData();
        $fieldArray = [];
        foreach ($data as $key => $value) {
            if (strstr((string)$key, 'field_')) {
                $fieldId              = str_replace('field_', '', (string)$key);
                $field                = $this->fieldRepository->getById($fieldId);
                $fieldArray[$fieldId] = $field->convertRawValue($value, ['preserveFrontend' => $preserveFrontend]);
            }
        }
        $this->setData('field', $fieldArray);
        return $this;
    }

    /**
     * @param array $options
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function toHtml(array $options = []): string
    {
        $webform = $this->getForm();
        $this->addFieldArray(true);

        if (!empty($options['template'])) {
            $this->viewBlock->setTemplate($options['template']);
        }

        if (!empty($options['data'])) {
            $this->viewBlock->setData($options['data']);
        }

        if (!isset($options['skip_fields'])) {
            $options['skip_fields'] = [];
        }

        if (!isset($options['adminhtml_downloads'])) {
            $options['adminhtml_downloads'] = false;
        }

        if (!isset($options['explicit_links'])) {
            $options['explicit_links'] = false;
        }

        if (!isset($options['header'])) {
            $options['header'] = $webform->getIsEmailHeaderEnabled();
        }

        $header = $options['header'] ? $this->getHeaderBlock($options) : '';
        return
            $header .
            $this->viewBlock
                ->setResult($this)
                ->setOptions($options)
                ->toHtml();
    }

    public function getHeaderBlock($options = []): string
    {
        return $this->infoBlock
            ->setResult($this)
            ->setOptions($options)
            ->toHtml();
    }

    /**
     * @return DataObject
     */
    public function getTemplateResultVar()
    {
        $result = new DataObject([
            ResultInterface::ID => $this->getId(),
            ResultInterface::FORM_ID => $this->getFormId()
        ]);

        /** @var FieldInterface[] $fields */
        $fields = $this->fieldRepository->getListByWebformId($this->getFormId(), $this->getStoreId())->getItems();
        foreach ($fields as $field) {
            $value = $this->getData('field_' . $field->getId());
            if (is_string($value)) {
                $value = trim($value);
            }
            $data = new DataObject([
                'value' => $field->getValueForResultTemplate($value, $this->getId()),
                FieldInterface::NAME => $field->getName(),
                FieldInterface::RESULT_LABEL => $field->getResultLabel(),
            ]);
            $result->setData($field->getId(), $data);
            if ($field->getCode()) {
                $result->setData($field->getCode(), $data);
            }
        }
        return $this->convertPhrase($result);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerName(): string
    {
        $customer_name = [];
        $fields        = $this->fieldRepository->getListByWebformId($this->getFormId(),
            $this->getStoreId())->getItems();
        foreach ($fields as $field) {
            foreach ($this->getData() as $key => $value) {
                if (is_string($value)) {
                    $value = trim($value);
                }
                if ($key == 'field_' . $field->getId() && $value) {
                    if (
                        $field->getCode() == 'name' ||
                        $field->getCode() == 'firstname' ||
                        $field->getCode() == 'lastname' ||
                        $field->getCode() == 'middlename'
                    ) {
                        $customer_name[] = $value;
                    }
                }
            }
        }

        if (count($customer_name) == 0) {
            if ($this->getCustomerId()) {
                $customer = $this->customerRegistry->retrieve($this->getCustomerId());
                if ($customer->getId()) {
                    return $customer->getName();
                }
            }
        }

        if (count($customer_name) == 0) {

            // try to get $_POST[''] variable
            if ($this->request->getParam('firstname')) {
                $customer_name [] = $this->request->getParam('firstname');
            }

            if ($this->request->getParam('lastname')) {
                $customer_name [] = $this->request->getParam('lastname');
            }
        }

        if (count($customer_name) == 0) {
            return __('Guest')->render();
        }

        return implode(' ', $customer_name);
    }

    /**
     * Get customer
     *
     * @return Customer|CustomerInterface|bool
     * @throws NoSuchEntityException
     */
    public function getCustomer()
    {
        return !$this->getCustomerId() ? false : $this->customerRegistry->retrieve($this->getCustomerId());
    }

    /**
     * @return string
     */
    public function getStatusName(): string
    {
        foreach ($this->approvalStatus->toOptionArray() as $option) {
            if ($this->getApproved() == $option['value']) {
                return $option['label'];
            }
        }
        return __('No status');
    }

    /**
     * @return FileDropzoneInterface[]
     */
    public function getFiles(): array
    {
        return $this->fileDropzoneRepository->getListByResultId($this->getId())->getItems();
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSubject(): string
    {
        $webform      = $this->getForm();
        $webform_name = $webform->getName();

        //get default subject for admin
        $subject = __("Form [%1]", $webform_name)->render();

        //iterate through fields and build subject
        $subject_array       = [];
        $fields_to_fieldsets = $webform->getFieldsToFieldsets(true);
        $logic_rules         = $webform->getLogic(false);
        $this->addFieldArray();
        foreach ($fields_to_fieldsets as $fieldset) {
            /** @var FieldInterface|DataObject $field */
            foreach ($fieldset['fields'] as $field) {
                $target_field = [
                    "id" => 'field_' . $field->getId(),
                    'logic_visibility' => $field->getData('logic_visibility')
                ];
                if ($field->hasData('visible')) {
                    $field_visibility = $field->getData('visible');
                } else {
                    $field_visibility = $webform->getTargetVisibility($target_field, $logic_rules,
                        $this->getData('field'));
                }
                if ($field_visibility && $field->getIsEmailSubject()) {
                    foreach ($this->getData() as $key => $value) {
                        if ($key == 'field_' . $field->getId() && $value) {
                            $subject_array[] = $field->getValueForSubject($value);
                        }
                    }
                }
            }
        }
        if (count($subject_array) > 0) {
            $subject = implode(" / ", $this->convertPhrase($subject_array));
        }
        return $subject;
    }

    /**
     * @return bool|array
     */
    public function getContactArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $this->getFormId())
            ->addFilter(FieldInterface::TYPE, 'select_contact')
            ->create();

        /** @var SelectContact[] $fields */
        $fields = $this->fieldRepository->getList($searchCriteria, $this->getStoreId())->getItems();
        foreach ($fields as $field) {
            foreach ($this->getData() as $key => $value) {
                if ($key == 'field_' . $field->getId() && $value) {
                    return $field->getContactArray($value);
                }
            }
        }
        return false;
    }

    /**
     * @param array $arrAttributes
     * @param string $rootName
     * @param bool $addOpenTag
     * @param bool $addCdata
     * @return string
     * @throws LocalizedException
     */
    public function toXml(array $arrAttributes = [], $rootName = 'item', $addOpenTag = false, $addCdata = true): string
    {
        $webform = $this->getForm();

        if ($webform->getCode()) {
            $this->setData('webform_code', $webform->getCode());
        }

        $this->unsetData('field');
        foreach ($this->getData() as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $valKey => $valVal) {
                    $this->setData($key . '_' . $valKey, $valVal);
                }
                $this->unsetData($key);
            }
            if (strstr((string)$key, 'field_')) {
                $fieldId = (int)str_replace('field_', '', (string)$key);
                $field   = $this->fieldRepository->getById($fieldId, $this->getStoreId());
                $value   = $field->getValueForExport($value);
                $this->setData($key, $value);
                if (!empty($field) && $field->getCode()) {
                    $this->setData($field->getCode(), $value);
                }
            }
        }
        return parent::toXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Result::class);
    }
}
