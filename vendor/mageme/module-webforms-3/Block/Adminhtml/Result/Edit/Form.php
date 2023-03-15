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

namespace MageMe\WebForms\Block\Adminhtml\Result\Edit;

use IntlDateFormatter;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Colorpicker;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Customer;
use MageMe\WebForms\Block\Adminhtml\Result\Element\File;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Gallery;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Image;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Password;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Region;
use MageMe\WebForms\Block\Adminhtml\Result\Element\Time;
use MageMe\WebForms\Model\Result;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\System\Store;

/**
 *
 */
class Form extends Generic
{
    /**
     *
     */
    const INFO_CUSTOMER_IP = 'info_customer_ip';
    /**
     *
     */
    const INFO_CREATED_AT = 'info_created_at';
    /**
     *
     */
    const INFO_WEBFORM_NAME = 'info_webform_name';

    /**
     * @var Config
     */
    protected $wysiwygConfig;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Country
     */
    protected $sourceCountry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Store $systemStore
     * @param ResolverInterface $localeResolver
     * @param Country $sourceCountry
     * @param array $data
     */
    public function __construct(
        Context           $context,
        Registry          $registry,
        FormFactory       $formFactory,
        Config            $wysiwygConfig,
        Store             $systemStore,
        ResolverInterface $localeResolver,
        Country           $sourceCountry,
        array             $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->wysiwygConfig   = $wysiwygConfig;
        $this->systemStore     = $systemStore;
        $this->localeResolver = $localeResolver;
        $this->sourceCountry  = $sourceCountry;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('result_form');
        $this->setTitle(__('Result Information'));
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var ResultInterface|Result $result */
        $result = $this->_coreRegistry->registry('webforms_result');

        /** @var \MageMe\WebForms\Model\Form $modelForm */
        $modelForm = $this->_coreRegistry->registry('webforms_form');

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', ['_current' => true]),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setFieldNameSuffix('result');

        $result->addData([
            self::INFO_CUSTOMER_IP => $result->getCustomerIp(),
            self::INFO_CREATED_AT => $this->_localeDate->formatDate($result->getCreatedAt(), IntlDateFormatter::MEDIUM,
                true),
            self::INFO_WEBFORM_NAME => $modelForm->getName(),
        ]);

        $fieldsetLegend = $result->getId() ? __('Result # %1', $result->getId()) : __('New Result');
        $fieldset       = $form->addFieldset('result_info', ['legend' => $fieldsetLegend]);
        $this->prepareResultInfoFieldset($result, $modelForm, $fieldset);

        $fields_to_fieldsets = $modelForm->getFieldsToFieldsets(true);

        foreach ($fields_to_fieldsets as $fs_id => $fs_data) {
            $legend = "";
            if (!empty($fs_data['name'])) {
                $legend = $fs_data['name'];
            }

            // check logic visibility
            $fieldset = $form->addFieldset('fs_' . $fs_id, [
                'legend' => $legend,
                'fieldset_container_id' => 'fieldset_' . $fs_id . '_container'
            ]);

            /** @var FieldInterface $field */
            foreach ($fs_data['fields'] as $field) {
                $fieldUi = $field->getFieldUi();
                if (!($fieldUi instanceof FieldResultFormInterface)) continue;
                $config = $fieldUi->getResultAdminFormConfig($result);
                if (!isset($config['type'])) {
                    continue;
                }
                if (in_array($config['type'], ['checkboxes', 'multiselect'])) {
                    $value = explode("\n", (string)$result->getData('field_' . $field->getId()));
                    $result->setData('field_' . $field->getId(), $value);
                }
                $config = new DataObject($config);
                $this->addFieldTypes($fieldset);

                $this->_eventManager->dispatch('webforms_block_adminhtml_results_edit_form_prepare_layout_field',
                    ['form' => $form, 'fieldset' => $fieldset, 'field' => $field, 'config' => $config]);
                $fieldset->addField('field_' . $field->getId(), $config->getData('type'), $config->getData());
            }
        }

        foreach ($modelForm->_getHidden() as $hiddenField) {
            $form->addField('field_' . $hiddenField->getId(), 'hidden', [
                'name' => 'field[' . $hiddenField->getId() . ']'
            ]);
        }

        $form->addValues($result->getData());

        $form->addField(ResultInterface::ID, 'hidden',
            [
                'name' => ResultInterface::ID,
                'value' => $result->getId(),
            ]);

        $form->addField(ResultInterface::FORM_ID, 'hidden',
            [
                'name' => ResultInterface::FORM_ID,
                'value' => $modelForm->getId(),
            ]);

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param ResultInterface|Result $result
     * @param FormInterface|\MageMe\WebForms\Model\Form $form
     * @param Fieldset $fieldset
     */
    public function prepareResultInfoFieldset(ResultInterface $result, FormInterface $form, Fieldset $fieldset)
    {
        $fieldset->addField(self::INFO_WEBFORM_NAME, 'link', [
            'id' => self::INFO_WEBFORM_NAME,
            'class' => 'control-value special',
            'href' => $this->getUrl('*/form/edit', [ResultInterface::FORM_ID => $form->getId()]),
            'label' => __('Web-form'),
        ]);

        if ($result->getId()) {
            $fieldset->addField(self::INFO_CREATED_AT, 'label', [
                'id' => self::INFO_CREATED_AT,
                'label' => __('Result Date'),
            ]);
        }

        $fieldset->addType('customer', Customer::class);

        $fieldset->addField(
            ResultInterface::CUSTOMER_ID, 'customer',
            [
                'label' => __('Customer'),
                'name' => ResultInterface::CUSTOMER_ID,
            ]
        );

        $fieldset->addField(
            'store_id', 'select',
            [
                'name' => 'store_id',
                'label' => __('Store View'),
                'values' => $this->systemStore->getStoreValuesForForm(false, false),
                'required' => true,
            ]
        );

        if ($result->getId()) {
            if ($this->_scopeConfig->getValue('webforms/general/collect_customer_ip', ScopeInterface::SCOPE_STORE,
                $result->getStoreId())) {
                $fieldset->addField(
                    Form::INFO_CUSTOMER_IP, 'label',
                    [
                        'id' => Form::INFO_CUSTOMER_IP,
                        'label' => __('Sent from IP'),
                    ]
                );
            }
        }

        foreach ($form->_getHidden() as $hiddenField) {
            $fieldset->addField('hiddenfield_' . $hiddenField->getId(), 'label', [
                'label' => $hiddenField->getName(),
                'name' => 'hiddenField[' . $hiddenField->getId() . ']',
                'value' => nl2br((string)$result->getData('field_' . $hiddenField->getId()))
            ]);
        }

        foreach ($this->getExtendedData() as $item) {
            $fieldset->addField($item['code'], 'label', [
                'label' => $item['label'],
                'value' => $item['value']
            ]);
        }
    }

    /**
     * Get extended result data array [['code'=> string, 'label' => string , 'value' => string], ...]
     * @return array
     */
    public function getExtendedData(): array
    {
        return [];
    }

    /**
     * @param Fieldset $fieldset
     */
    public function addFieldTypes(Fieldset $fieldset) {
        $fieldset->addType(Image::TYPE, Image::class);
        $fieldset->addType(File::TYPE, File::class);
        $fieldset->addType(Gallery::TYPE, Gallery::class);
        $fieldset->addType(Region::TYPE, Region::class);
        $fieldset->addType(Colorpicker::TYPE, Colorpicker::class);
        $fieldset->addType(Time::TYPE, Time::class);
        $fieldset->addType(Password::TYPE, Password::class);
    }
}
