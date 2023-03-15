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
use Magento\Ui\Component\Form;

class SelectCheckbox extends Select
{
    const MIN_OPTIONS = Type\SelectCheckbox::MIN_OPTIONS;
    const MAX_OPTIONS = Type\SelectCheckbox::MAX_OPTIONS;
    const MIN_OPTIONS_ERROR_TEXT = Type\SelectCheckbox::MIN_OPTIONS_ERROR_TEXT;
    const MAX_OPTIONS_ERROR_TEXT = Type\SelectCheckbox::MAX_OPTIONS_ERROR_TEXT;
    const IS_INTERNAL_ELEMENTS_INLINE = Type\SelectCheckbox::IS_INTERNAL_ELEMENTS_INLINE;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Textarea::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Options'),
                                    'additionalInfo' => __('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Use <i>Option Text {{null}}</i> to create option without value</i><br>Use <i>Option Text {{val VALUE}}</i> to set different option value'),
                                    'rows' => 5,
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MIN_OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MIN_OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Minimum Selected Options'),
                                    'additionalInfo' => __('Minimum allowed options'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MAX_OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MAX_OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Maximum Selected Options'),
                                    'additionalInfo' => __('Maximum allowed options'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MIN_OPTIONS_ERROR_TEXT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::MIN_OPTIONS_ERROR_TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Minimum Selected Options Error Text'),
                                    'additionalInfo' => __('Minimum allowed options error text'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MAX_OPTIONS_ERROR_TEXT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::MAX_OPTIONS_ERROR_TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 69,
                                    'label' => __('Maximum Selected Options Error Text'),
                                    'additionalInfo' => __('Maximum allowed options error text'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_INTERNAL_ELEMENTS_INLINE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_INTERNAL_ELEMENTS_INLINE,
                                    'visible' => 0,
                                    'sortOrder' => 70,
                                    'label' => __('Inline Elements'),
                                    'additionalInfo' => __('Display elements of the field inline instead of the column'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        return $this->getDefaultUIResultColumnConfig($sortOrder);
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config            = $this->getDefaultResultAdminFormConfig();
        $config['type']    = 'checkboxes';
        $config['options'] = $this->getField()->getOptionsArray();
        $config['name']    = 'field[' . $this->getField()->getId() . '][]';
        return $config;
    }
}
