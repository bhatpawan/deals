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

namespace MageMe\WebForms\Model\Field;


use Exception;
use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Api\ResultValueRepositoryInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Config\Config as FieldConfig;
use MageMe\WebForms\Helper\CssHelper;
use MageMe\WebForms\Model\AbstractModel;
use MageMe\WebForms\Model\Field\Type\AbstractOption;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\ResourceModel\Field;
use MageMe\WebForms\Model\Store;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\Template\Simple;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\Regex;
use Zend_Filter_Exception;
use Zend_Validate_Exception;

abstract class AbstractField extends AbstractModel implements FieldInterface
{
    /**
     * Field cache tag
     */
    const CACHE_TAG = 'webforms_field';

    /**
     * @inheritdoc
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $tooltip_regex = "/{{tooltip}}(.*?){{\\/tooltip}}/si";

    /**
     * @var string
     */
    protected $tooltip_option_regex = "/{{tooltip\s*val=\"(.*?)\"}}(.*?){{\\/tooltip}}/si";

    /**
     * @var string
     */
    protected $tooltip_clean_regex = "/{{tooltip(.*?)}}(.*?){{\\/tooltip}}/si";

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
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var FieldConfig
     */
    protected $fieldConfig;

    /**
     * @var FieldBlockInterface
     */
    protected $fieldBlock;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * @var ResultValueRepositoryInterface
     */
    protected $resultValueRepository;

    /**
     * @var CssHelper
     */
    protected $cssHelper;

    /**
     * @var FieldUiInterface
     */
    protected $fieldUi;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * AbstractField constructor.
     *
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
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
        $this->scopeConfig           = $context->getScopeConfig();
        $this->session               = $context->getSession();
        $this->dateHelper            = $context->getDateHelper();
        $this->formKey               = $context->getFormKey();
        $this->filterProvider        = $context->getFilterProvider();
        $this->fieldConfig           = $context->getFieldConfig();
        $this->urlBuilder            = $context->getUrlBuilder();
        $this->request               = $context->getRequest();
        $this->fieldFactory          = $context->getFieldFactory();
        $this->fieldRepository       = $context->getFieldRepository();
        $this->formRepository        = $context->getFormRepository();
        $this->logicRepository       = $context->getLogicRepository();
        $this->resultValueRepository = $context->getResultValueRepository();
        $this->cssHelper             = $context->getCssHelper();
        $this->categoryRepository    = $context->getCategoryRepository();
        $this->productRepository     = $context->getProductRepository();
        $this->fieldBlock            = $fieldBlock;
        $this->fieldUi               = $fieldUi;
    }

    /**
     * get form key
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @inheritDoc
     */
    public function toHtml()
    {
        // Replace value with pre-filled data for registered customer based on codes {{...}}
        if (is_string($this->getValue()) && !($this instanceof AbstractOption)) {
            $this->setValue($this->replaceCodesWithData($this->getValue()));
        }
        $this->fieldBlock->setField($this);
        $this->fieldBlock->setResult($this->getData('result'));
        $html        = $this->fieldBlock->toHtml();
        $html_object = new DataObject(['html' => $html]);
        $this->_eventManager->dispatch('webforms_fields_tohtml_html',
            ['field' => $this, 'html_object' => $html_object]);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $html_object->getHtml();
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        $value = $this->fieldConfig->getTypeValue($this->getType());
        return $value ? $this->getData($value) : null;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value): FieldInterface
    {
        return $this->setData($this->fieldConfig->getTypeValue($this->getType()), $value);
    }

    /**
     * @inheritdoc
     */
    public function setData($key, $value = null)
    {
        if (is_array($key) && isset($key[self::TYPE])) {
            if (isset($this->_data[self::TYPE])) {
                $key[self::TYPE] = $this->_data[self::TYPE];
            }
        }
        return parent::setData($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function replaceCodesWithData(string $value): string
    {
        try {
            //preserve the {{...}} variables and codes and apply simple filter
            $codes        = ['var', 'customVar', 'block', 'widget', 'layout'];
            $origCodes    = array_map(function ($el) {
                return '{{' . $el;
            }, $codes);
            $replaceCodes = array_map(function ($el) {
                return '\{\{' . $el;
            }, $codes);
            $value        = @$this->getFilter()->filter(str_replace($origCodes, $replaceCodes, $value));
            $value        = str_replace($replaceCodes, $origCodes, $value);

            $filter  = $this->filterProvider->getPageFilter();
            $product = false;
            if ($this->session->getData('last_viewed_product_id')) {
                $product = $this->productRepository->getById($this->session->getData('last_viewed_product_id'));
            }
            $category = false;
            if ($this->session->getData('last_viewed_category_id')) {
                $category = $this->categoryRepository->get($this->session->getData('last_viewed_category_id'));
            }
            $url = $this->session->getData('last_viewed_url');
            $filter->setVariables([
                'product' => $product,
                'category' => $category,
                'customer' => $this->getCustomer(),
                'core_session' => $this->session,
                'customer_session' => $this->session,
                'url' => $url
            ]);

            return $filter->filter($value);
        } catch (Zend_Filter_Exception $e) {
            return $value;
        }
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->session->getCustomer();
    }

    /**
     * @inheritDoc
     */
    public function getFilter(): Simple
    {
        $filter = new Simple;

        $customer = $this->getCustomer();
        if ($customer->getId()) {
            if ($customer->getDefaultBillingAddress()) {
                foreach ($customer->getDefaultBillingAddress()->getData() as $key => $value) {
                    $filter->setData($key, $value);
                    if ($key == 'street') {
                        $streetArray = explode("\n", (string)$value);
                        for ($i = 0; $i < count($streetArray); $i++) {
                            $filter->setData('street_' . ($i + 1), $streetArray[$i]);
                        }
                    }
                }
            }

            $customerData = $customer->getData();
            foreach ($customerData as $key => $value) {
                $filter->setData($key, $value);
            }
        }

        return $filter;
    }

    /**
     * @return FieldUiInterface
     */
    public function getFieldUi(): FieldUiInterface
    {
        return $this->fieldUi->setField($this);
    }

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $rules        = [];
        $descriptions = [];

        // Required
        if ($this->getIsRequired()) {
            $rules['required-entry'] = "'required-entry':true";
            if ($this->getValidationRequiredMessage()) {
                $descriptions['data-msg-required-entry'] = $this->getValidationRequiredMessage();
            }
        }

        // Regex
        if ($this->getRegexValidationPattern()) {
            $flags = [];

            $regexp = trim((string)$this->getRegexValidationPattern());

            preg_match('/\/([igmy]{1,4})$/', $regexp, $flags);

            // set regex flags
            if (!empty($flags[1])) {
                $flags  = (string)$flags[1];
                $regexp = substr($regexp, 0, strlen($regexp) - strlen($flags));
            } else {
                $flags = '';
            }

            if (substr($regexp, 0, 1) == '/'
                && substr($regexp, strlen($regexp) - 1, strlen($regexp)) == '/') {
                $regexp = substr($regexp, 1, -1);
            }
            $regexp           = str_replace('\\', '\\\\', $regexp);
            $regexp           = trim(str_replace("'", "\'", $regexp));
            $validate_message = $this->getRegexValidationMessage();

            $rules['mm-pattern'] = "'mm-pattern':'" . $regexp . "'";
            if ($validate_message) {
                $descriptions['data-msg-mm-pattern'] = $this->getRegexValidationMessage();
            }
            if ($flags) {
                $descriptions['data-mm-pattern-flags'] = $flags;
            }
        }

        // Min length
        if ($this->getMinLength()) {
            $rules['validate-length-min'] = "'validate-length-min':'" . $this->getMinLength() . "'";
            if ($this->getMinLengthValidationMessage()) {
                $descriptions['data-msg-validate-length-min'] = $this->getMinLengthValidationMessage();
            }
        }

        // Max length
        if ($this->getMaxLength()) {
            $rules['validate-length-max'] = "'validate-length-max':'" . $this->getMaxLength() . "'";
            if ($this->getMaxLengthValidationMessage()) {
                $descriptions['data-msg-validate-length-max'] = $this->getMaxLengthValidationMessage();
            }
        }

        return ['rules' => $rules, 'descriptions' => $descriptions];
    }

    /**
     * @inheritDoc
     */
    public function getIsRequired(): bool
    {
        return (bool)$this->getData(self::IS_REQUIRED);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRequiredMessage(): ?string
    {
        return (string)$this->getData(self::VALIDATION_REQUIRED_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function getRegexValidationPattern(): ?string
    {
        return $this->getData(self::REGEX_VALIDATION_PATTERN);
    }

    /**
     * @inheritDoc
     */
    public function getRegexValidationMessage(): ?string
    {
        return (string)$this->getData(self::REGEX_VALIDATION_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function getMinLength(): ?int
    {
        return $this->getData(self::MIN_LENGTH);
    }

    /**
     * @inheritDoc
     */
    public function getMinLengthValidationMessage(): ?string
    {
        return (string)$this->getData(self::MIN_LENGTH_VALIDATION_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function getMaxLength(): ?int
    {
        return $this->getData(self::MAX_LENGTH);
    }

#region DB getters and setters

    /**
     * @inheritDoc
     */
    public function getMaxLengthValidationMessage(): ?string
    {
        return (string)$this->getData(self::MAX_LENGTH_VALIDATION_MESSAGE);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
    }

    /**
     * @inheritDoc
     */
    public function getDisplayOptions(): array
    {
        return [
            ['value' => 'on', 'label' => __('On')],
            ['value' => 'off', 'label' => __('Off')],
            ['value' => 'value', 'label' => __('Value only')],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $customerValue = $this->getCustomerValue();
        return $customerValue ?: trim((string)$this->getValue());
    }

    /**
     * @return bool|string
     */
    public function getCustomerValue()
    {
        $result        = $this->getData('result');
        $customerValue = $result ? $result->getData('field_' . $this->getId()) : false;
        try {
            if ($this->getForm()->getIsUrlParametersAccepted()) {
                $requestValue = trim(strval($this->request->getParam($this->getCode())));
                if ($requestValue) {
                    $customerValue = $requestValue;
                }
            }
        } catch (LocalizedException $e) {
            return false;
        }
        return $customerValue;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getForm(): FormInterface
    {
        if ($this->getStoreId()) {
            return $this->formRepository->getById($this->getFormId(), $this->getStoreId());
        }
        return $this->formRepository->getById($this->getFormId());
    }

    /**
     * @inheritDoc
     */
    public function getFormId(): ?int
    {
        return $this->getData(self::FORM_ID);
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
    public function getValueForSubject($value)
    {
        return htmlentities((string)$value);
    }

    /**
     * @inheritDoc
     */
    public function getLogic(): array
    {
        return $this->logicRepository->getListByFieldId($this->getId(), $this->getStoreId())->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getLogicTargetOptionsArray(): array
    {
        $options             = [];
        $webform             = $this->getForm();
        $fields_to_fieldsets = $webform->getFieldsToFieldsets(true);
        $searchCriteria      = $this->searchCriteriaBuilder
            ->addFilters([
                $this->filterBuilder
                    ->setField(LogicInterface::TARGET_SERIALIZED)
                    ->setConditionType('like')
                    ->setValue('%"field_' . $this->getId() . '"%')
                    ->create()
            ])
            ->create();
        /** @var LogicInterface[] $logic */
        $logic = $this->logicRepository->getList($searchCriteria)->getItems();

        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $field_options = [];
            foreach ($fieldset['fields'] as $field) {
                $skip = false;
                foreach ($logic as $rule) {
                    if ($rule->getFieldId() == $field->getId()) {
                        $skip = true;
                    }
                }
                if ($field->getId() != $this->getId() && $field->getType() != 'hidden' && !$skip) {
                    $field_options[] = ['value' => 'field_' . $field->getId(), 'label' => $field->getName()];
                }
            }

            if ($fieldset_id) {
                if ($this->getFieldsetId() != $fieldset_id) {
                    $options[] = [
                        'value' => 'fieldset_' . $fieldset_id,
                        'label' => $fieldset['name'] . ' [' . __('Field Set') . ']'
                    ];
                }
                if (count($field_options)) {
                    $options[] = ['value' => $field_options, 'label' => $fieldset['name']];
                }
            } else {
                foreach ($field_options as $opt) {
                    $options[] = $opt;
                }
            }
        }
        $options[] = ['value' => 'submit', 'label' => '[' . strtoupper(__('Submit')) . ']'];

        return $options;
    }

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
    public function getFieldsetId(): ?int
    {
        return $this->getData(self::FIELDSET_ID);
    }

    /**
     * @inheritDoc
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function duplicate(): FieldInterface
    {
        return $this->clone([
            FieldInterface::FORM_ID => $this->getFormId(),
            FieldInterface::NAME => $this->getName() . ' ' . __('(new copy)'),
            FieldInterface::IS_ACTIVE => false
        ]);
    }

    /**
     * @inheritDoc
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function clone(array $parameters = []): FieldInterface
    {
        // clone field
        $field = $this->fieldFactory->create($this->getType());
        $field->setData($this->getData())
            ->setId(null)
            ->setCreatedAt(null)
            ->setUpdatedAt(null);
        foreach ($parameters as $key => $data) {
            switch ($key) {
                case FieldInterface::FIELDSET_ID:
                {
                    $field->setFieldsetId($data);
                    break;
                }
                case FieldInterface::FORM_ID:
                {
                    $field->setFormId($data);
                    break;
                }
                case FieldInterface::NAME:
                {
                    $field->setName($data);
                    break;
                }
                case FieldInterface::IS_ACTIVE:
                {
                    $field->setIsActive($data);
                    break;
                }
            }
        }
        $this->fieldRepository->save($field);

        // duplicate store data
        $stores = $this->storeRepository->getListByEntity($this->getEntityType(), $this->getId())->getItems();

        /** @var StoreInterface|Store $store */
        foreach ($stores as $store) {
            $newStore = $this->storeFactory->create()
                ->setData($store->getData())
                ->setId(null)
                ->setEntityId($field->getId());
            $this->storeRepository->save($newStore);
        }

        return $field;
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): FieldInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): FieldInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function setFieldsetId(?int $fieldsetId): FieldInterface
    {
        return $this->setData(self::FIELDSET_ID, $fieldsetId);
    }

    /**
     * @inheritDoc
     */
    public function setFormId(int $formId): FieldInterface
    {
        return $this->setData(self::FORM_ID, $formId);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): FieldInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive(bool $isActive): FieldInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    public function getTooltip($option = false)
    {
        $matches = [];
        $pattern = $this->tooltip_regex;
        $comment = (string)$this->getData(self::COMMENT);

        if ($option) {
            $pattern = $this->tooltip_option_regex;
            preg_match_all($pattern, $comment, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $i => $match) {
                    if (trim((string)$match) == trim((string)$option)) {
                        return $matches[2][$i];
                    }
                }
            }
            return false;
        }

        if ($comment) preg_match($pattern, $comment, $matches);

        if (!empty($matches[1])) {
            return trim((string)$matches[1]);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function setCode(?string $code): FieldInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getComment(): ?string
    {
        $comment = (string)$this->getData(self::COMMENT);
        $subject = preg_replace($this->tooltip_clean_regex, "", $comment);
        if (is_string($subject)) {
            $subject = trim($subject);
        }
        $text   = $comment ? $subject : '';
        $filter = $this->filterProvider->getPageFilter();
        if ($text) {
            return $filter->filter($text);
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function setComment(?string $comment): FieldInterface
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getResultLabel(): ?string
    {
        $resultLabel = $this->getData(self::RESULT_LABEL);
        if ($resultLabel) {
            return $resultLabel;
        }
        return $this->getName();
    }

    /**
     * @inheritDoc
     */
    public function setResultLabel(?string $resultLabel): FieldInterface
    {
        return $this->setData(self::RESULT_LABEL, $resultLabel);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayInResult(): string
    {
        return (string)$this->getData(self::DISPLAY_IN_RESULT);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayInResult(string $value): FieldInterface
    {
        return $this->setData(self::DISPLAY_IN_RESULT, $value);
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): FieldInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getIsEmailSubject(): bool
    {
        return (bool)$this->getData(self::IS_EMAIL_SUBJECT);
    }

    /**
     * @inheritDoc
     */
    public function setIsEmailSubject(bool $isEmailSubject): FieldInterface
    {
        return $this->setData(self::IS_EMAIL_SUBJECT, $isEmailSubject);
    }

    /**
     * @inheritDoc
     */
    public function getCssInputClass(): ?string
    {
        return $this->getData(self::CSS_INPUT_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setCssInputClass(?string $cssInputClass): FieldInterface
    {
        return $this->setData(self::CSS_INPUT_CLASS, $cssInputClass);
    }

    /**
     * @inheritDoc
     */
    public function getCssContainerClass(): ?string
    {
        return implode(" ", [
            $this->getData(self::CSS_CONTAINER_CLASS),
            $this->getResponsiveCss(),
            $this->getTypeCssForContainer()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getResponsiveCss(): string
    {
        return $this->cssHelper->getResponsiveCss($this->getWidthProportionLg(), $this->getWidthProportionMd(),
            $this->getWidthProportionSm(),
            $this->getIsDisplayedInNewRowLg(), $this->getIsDisplayedInNewRowMd(), $this->getIsDisplayedInNewRowSm());
    }

    /**
     * @inheritDoc
     */
    public function getWidthProportionLg(): ?string
    {
        return $this->getData(self::WIDTH_PROPORTION_LG);
    }

    /**
     * @inheritDoc
     */
    public function getWidthProportionMd(): ?string
    {
        return $this->getData(self::WIDTH_PROPORTION_MD);
    }

    /**
     * @inheritDoc
     */
    public function getWidthProportionSm(): ?string
    {
        return $this->getData(self::WIDTH_PROPORTION_SM);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedInNewRowLg(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_IN_NEW_ROW_LG);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedInNewRowMd(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_IN_NEW_ROW_MD);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedInNewRowSm(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_IN_NEW_ROW_SM);
    }

    /**
     * @inheritDoc
     */
    public function getTypeCssForContainer(): string
    {
        return 'type-' . $this->getType();
    }

    /**
     * @inheritDoc
     */
    public function setCssContainerClass(?string $cssContainerClass): FieldInterface
    {
        return $this->setData(self::CSS_CONTAINER_CLASS, $cssContainerClass);
    }

    /**
     * @inheritDoc
     */
    public function getCssInputStyle(): ?string
    {
        return $this->getData(self::CSS_INPUT_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setCssInputStyle(?string $cssInputStyle): FieldInterface
    {
        return $this->setData(self::CSS_INPUT_STYLE, $cssInputStyle);
    }

    /**
     * @inheritDoc
     */
    public function setRegexValidationMessage(?string $regexValidationMessage): FieldInterface
    {
        return $this->setData(self::REGEX_VALIDATION_MESSAGE, $regexValidationMessage);
    }

    /**
     * @inheritDoc
     */
    public function setRegexValidationPattern(?string $regexValidationPattern): FieldInterface
    {
        return $this->setData(self::REGEX_VALIDATION_PATTERN, $regexValidationPattern);
    }

    /**
     * @inheritDoc
     */
    public function setMinLength(?int $minLength): FieldInterface
    {
        return $this->setData(self::MIN_LENGTH, $minLength);
    }

    /**
     * @inheritDoc
     */
    public function setMaxLength(?int $maxLength): FieldInterface
    {
        return $this->setData(self::MAX_LENGTH, $maxLength);
    }

    /**
     * @inheritDoc
     */
    public function setMinLengthValidationMessage(?string $minLengthValidationMessage): FieldInterface
    {
        return $this->setData(self::MIN_LENGTH_VALIDATION_MESSAGE, $minLengthValidationMessage);
    }

    /**
     * @inheritDoc
     */
    public function setMaxLengthValidationMessage(?string $maxLengthValidationMessage): FieldInterface
    {
        return $this->setData(self::MAX_LENGTH_VALIDATION_MESSAGE, $maxLengthValidationMessage);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): ?int
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritDoc
     */
    public function setPosition(?int $position): FieldInterface
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function setIsRequired(bool $isRequired): FieldInterface
    {
        return $this->setData(self::IS_REQUIRED, $isRequired);
    }

    /**
     * @inheritDoc
     */
    public function setValidationRequiredMessage(?string $validationRequiredMessage): FieldInterface
    {
        return $this->setData(self::VALIDATION_REQUIRED_MESSAGE, $validationRequiredMessage);
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
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setIsUnique(bool $isUnique): FieldInterface
    {
        return $this->setData(self::IS_UNIQUE, $isUnique);
    }

    /**
     * @inheritDoc
     */
    public function setUniqueValidationMessage(?string $uniqueValidationMessage): FieldInterface
    {
        return $this->setData(self::UNIQUE_VALIDATION_MESSAGE, $uniqueValidationMessage);
    }

    /**
     * @inheritDoc
     */
    public function getBrowserAutocomplete(): ?string
    {
        return $this->getData(self::BROWSER_AUTOCOMPLETE);
    }

    /**
     * @inheritDoc
     */
    public function setBrowserAutocomplete(?string $browserAutocomplete): FieldInterface
    {
        return $this->setData(self::BROWSER_AUTOCOMPLETE, $browserAutocomplete);
    }

    /**
     * @inheritDoc
     */
    public function getIsLabelHidden(): bool
    {
        return (bool)$this->getData(self::IS_LABEL_HIDDEN);
    }

    /**
     * @inheritDoc
     */
    public function setIsLabelHidden(bool $isLabelHidden): FieldInterface
    {
        return $this->setData(self::IS_LABEL_HIDDEN, $isLabelHidden);
    }

    /**
     * @inheritDoc
     */
    public function setWidthProportionLg(?string $widthProportionLg): FieldInterface
    {
        return $this->setData(self::WIDTH_PROPORTION_LG, $widthProportionLg);
    }

    /**
     * @inheritDoc
     */
    public function setWidthProportionMd(?string $widthProportionMd): FieldInterface
    {
        return $this->setData(self::WIDTH_PROPORTION_MD, $widthProportionMd);
    }

    /**
     * @inheritDoc
     */
    public function setWidthProportionSm(?string $widthProportionSm): FieldInterface
    {
        return $this->setData(self::WIDTH_PROPORTION_SM, $widthProportionSm);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedInNewRowLg(bool $isDisplayedInNewRowLg): FieldInterface
    {
        return $this->setData(self::IS_DISPLAYED_IN_NEW_ROW_LG, $isDisplayedInNewRowLg);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedInNewRowMd(bool $isDisplayedInNewRowMd): FieldInterface
    {
        return $this->setData(self::IS_DISPLAYED_IN_NEW_ROW_MD, $isDisplayedInNewRowMd);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedInNewRowSm(bool $isDisplayedInNewRowSm): FieldInterface
    {
        return $this->setData(self::IS_DISPLAYED_IN_NEW_ROW_SM, $isDisplayedInNewRowSm);
    }

    /**
     * @inheritDoc
     */
    public function getCustomAttributes(): ?string
    {
        return $this->getData(self::CUSTOM_ATTRIBUTES);
    }

    /**
     * @inheritDoc
     */
    public function setCustomAttributes(?string $customAttributes): FieldInterface
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $customAttributes);
    }

    /**
     * @inheritDoc
     */
    public function getTypeAttributesSerialized(): ?string
    {
        return $this->getData(self::TYPE_ATTRIBUTES_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setTypeAttributesSerialized(?string $typeAttributesSerialized): FieldInterface
    {
        return $this->setData(self::TYPE_ATTRIBUTES_SERIALIZED, $typeAttributesSerialized);
    }

#endregion

    /**
     * @inheritDoc
     */
    public function loadTypeAttributesFormJSON(?string $json): FieldInterface
    {
        $typeAttributes = $this->getTypeAttributesNames();
        $arr            = $json ? json_decode($json, true) : [];
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                if (in_array($key, $typeAttributes)) {
                    $this->setData($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTypeAttributesNames(): array
    {
        return $this->fieldConfig->getTypeAttributes($this->getType());
    }

    /**
     * @inheritDoc
     */
    public function getTypeAttributesAsJSON(): ?string
    {
        $typeAttributes = $this->getTypeAttributesNames();
        $resultArr      = [];
        foreach ($typeAttributes as $attribute) {
            $resultArr[$attribute] = $this->getData($attribute);
        }
        return count($resultArr) ? json_encode($resultArr) : null;
    }

    /**
     * @inheritDoc
     * @throws Zend_Validate_Exception
     */
    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array
    {
        $errors = [];

        // check required
        if ($this->getIsRequired()) {
            if ($this->validatePostRequired($postData, $logicVisibility)) {

                // check custom validation
                if ($this->getRegexValidationPattern()) {
                    if (!$this->_validatePostRegex($postData, $logicVisibility)) {
                        $errors[] = $this->getName() . ": " . $this->getRegexValidationMessage();
                    }
                }
            } else {
                $errorMsg = $this->getValidationRequiredMessage();
                $errors[] = $errorMsg ?: __('%1 is required', $this->getName());
            }
        }

        // check unique
        if ($this->getIsUnique()) {
            if (!$this->_validatePostUnique($postData)) {
                $errorMsg = $this->getUniqueValidationMessage();
                $errors[] = $errorMsg ?: __('Duplicate value has been found: %1', $postData['field'][$this->getId()]);
            }
        }

        return $errors;
    }

    /**
     * Check required
     *
     * @param array $postData
     * @param bool $logicVisibility
     * @return bool
     */
    public function validatePostRequired(array $postData, bool $logicVisibility): bool
    {
        $placeholder = htmlspecialchars(trim((string)$this->getPlaceholder()));
        $fields      = $postData['field'];

        // if field is required but is not in the post array
        if (!isset($fields[$this->getId()])) {
            return !$logicVisibility;
        }

        $value = $fields[$this->getId()];
        if (is_array($value)) {
            $value = implode(" ", $value);
        }
        $value = trim(strval($value));

        return !($logicVisibility && ($value == $placeholder || $value == ''));
    }

    /**
     * Check Regex
     *
     * @param array $postData
     * @param bool $logicVisibility
     * @return bool
     * @throws Zend_Validate_Exception
     */
    protected function _validatePostRegex(array $postData, bool $logicVisibility): bool
    {
        if ($this->getIsActive() && $logicVisibility) {
            $pattern = trim((string)$this->getRegexValidationPattern());

            // clear global modifier
            if (substr($pattern, 0, 1) == '/' && substr($pattern, -2) == '/g') {
                $pattern = substr($pattern, 0, strlen($pattern) - 1);
            }

            $status = @preg_match($pattern, "Test");
            if (false === $status) {
                $pattern = "/" . $pattern . "/";
            }
            $validate = new Regex($pattern);
            $fields   = $postData['field'];
            foreach ($fields as $id => $value) {
                if ($id == $this->getId() && !$validate->isValid($value)) {
                    return false;
                }
            }
        }
        return true;
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
    public function getIsUnique(): bool
    {
        return (bool)$this->getData(self::IS_UNIQUE);
    }

    /**
     * Check unique
     *
     * @param array $postData
     * @return bool
     */
    protected function _validatePostUnique(array $postData): bool
    {
        $fields = $postData['field'];
        if ($this->getIsActive() && !empty($fields[$this->getId()])) {

            /** @var ResultValueInterface[] $values */
            $values   = $this->resultValueRepository->getListByFieldId($this->getId())->getItems();
            $resultId = empty($postData[ResultInterface::ID]) ? 0 : (int)$postData[ResultInterface::ID];
            foreach ($values as $resultValue) {
                if ($resultValue->getValue() == $fields[$this->getId()] && $resultValue->getResultId() != $resultId) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getUniqueValidationMessage(): ?string
    {
        return $this->getData(self::UNIQUE_VALIDATION_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function getValueForExport($value, ?int $resultId = null)
    {
        return $this->getValueForResultTemplate($value, $resultId);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        return nl2br((string)$value);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminhtml($value, array $options = [])
    {
        return $this->getValueForResultHtml($value, $options);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return nl2br(htmlentities((string)$value));
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminGrid($value, array $options = [])
    {
        return $this->getValueForResultHtml($value, $options);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        if ($value == $this->getPlaceholder()) {
            $value = '';
        }
        if (is_array($value)) {
            $value = implode("\n", $value);
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultValueRenderer(DataObject $row): string
    {
        $fieldIndex = 'field_' . $this->getId();
        $value      = $row->getData($fieldIndex);
        return nl2br(htmlspecialchars((string)$value));
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultCollectionFilter($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getResultCollectionFilterCondition($value, string $prefix = '%'): string
    {
        $id = $this->getId();
        if (is_array($value)) {
            if (!empty($value['from']) && !empty($value['to'])) {
                return "results_values_$id.value >= $value[from] AND results_values_$id.value <= $value[to]";
            }
            if (!empty($value['from'])) {
                return "results_values_$id.value >= $value[from]";
            }
            if (!empty($value['to'])) {
                return "results_values_$id.value <= $value[to]";
            }
            if (!empty($value['in']) && is_array($value['in'])) {
                return "results_values_$id.value IN ('" . implode(
                        "','",
                        str_replace("'", "\'", $value['in'])
                    ) . "')";
            }
        }
        $searchValue = $this->getResultCollectionFilterConditionSearchValue($value);
        return "results_values_$id.value like '" . $prefix . $searchValue . $prefix . "'";
    }

    /**
     * Get search value for getResultCollectionFilterCondition function
     *
     * @param string $value
     * @return string
     */
    protected function getResultCollectionFilterConditionSearchValue(string $value): string
    {
        $search_value = trim(str_replace(["\\"], ["\\\\"], $value));
        return trim(str_replace(["'"], ["\\'"], $search_value));
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return nl2br(htmlentities($value));
    }

    /**
     * @inheritDoc
     */
    public function getLabelForForFormDefaultTemplate(string $uid): string
    {
        return "for=\"field" . $uid . $this->getId() . "\"";
    }

    /**
     * @inheritDoc
     */
    public function getIsLogicType(): bool
    {
        $result = $this->getData(self::IS_LOGIC_TYPE);
        if ($result == null) {
            $result = in_array($this->getType(), $this->fieldConfig->getLogicTypes());
        }
        return (bool)$result;
    }

    /**
     * @inheritDoc
     */
    public function processTypeAttributesOnSave(array &$data, int $storeId): FieldInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function processColumnDataSource(array &$dataSource): FieldInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function processNewResult(ResultInterface $result): FieldInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function preparePostData(
        array           &$postData,
        array           $config = [],
        ResultInterface $result = null,
        bool            $isAdmin = false
    ): FieldInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function processPostResult(ResultInterface $result): FieldInterface
    {
        return $this;
    }

    /**
     * @param $value
     * @param array $config
     * @return mixed
     */
    public function convertRawValue($value, array $config = [])
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function isImportPostProcess(): bool
    {
        return false;
    }

    /**
     * @param array $logicMatrix
     * @return $this
     */
    public function importPostProcess(array $logicMatrix): FieldInterface
    {
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function getPostValue(
        array $postData,
        array $config = [],
        bool  $visibility = true,
        bool  $emptyFieldArray = false
    )
    {
        if (!$visibility) {
            return '';
        }
        if ($emptyFieldArray
            && $this->getCode()
            && isset($postData[$this->getCode()])) {
            return $postData[$this->getCode()];

        }
        return $postData['field'][$this->getId()] ?? '';
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Field::class);
    }
}
