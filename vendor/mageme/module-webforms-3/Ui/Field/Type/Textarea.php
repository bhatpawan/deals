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

namespace MageMe\WebForms\Ui\Field\Type;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;
use Magento\Ui\Component\Form;

class Textarea extends Text
{
    const ROWS = Type\Textarea::ROWS;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::PLACEHOLDER => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::PLACEHOLDER,
                                    'visible' => 0,
                                    'sortOrder' => 45,
                                    'label' => __('Placeholder'),
                                    'additionalInfo' => __('Placeholder text will appear in the input and disappear on the focus'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::TEXT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Textarea::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Default Field Value'),
                                    'rows' => 5,
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::CUSTOMER_DATA => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::CUSTOMER_DATA,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Pre-fill With Customer Data'),
                                    'options' => $this->customerDataOptions->toOptionArray(),
                                    'caption' => __('-- Please Select --'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MASK => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::MASK,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Input Mask'),
                                    'additionalInfo' => __('<i>9</i> : numeric<br><i>a</i> : alphabetical<br><i>*</i> : alphanumeric<br><i>{min,max}</i> : how many times can a symbol be repeated, <i>min</i> and <i>max</i> are integer numbers<br><i>[***]</i> : optional part of the mask, hidden by default.'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::CASE_TRANSFORM => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::CASE_TRANSFORM,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Transform text after save'),
                                    'options' => $this->caseTransform->toOptionArray(),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::ROWS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::ROWS,
                                    'visible' => 0,
                                    'sortOrder' => 69,
                                    'label' => __('Rows'),
                                    'validation' => [
                                        'validate-digits' => true,
                                        'required-entry' => true,
                                    ],
                                    'default' => 5,
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        $config                  = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['component']     = 'MageMe_WebForms/js/grid/columns/textarea';
        $config['bodyTmpl']      = 'MageMe_WebForms/grid/columns/textarea';
        $config['disableAction'] = true;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config         = $this->getDefaultResultAdminFormConfig();
        $config['type'] = 'textarea';
        $config['rows'] = $this->getField()->getRows();
        return $config;
    }
}
