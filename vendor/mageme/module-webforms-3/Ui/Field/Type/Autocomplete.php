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


use Magento\Ui\Component\Form;

class Autocomplete extends Text
{
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
                                    'label' => __('Auto-complete Choices'),
                                    'additionalInfo' => __('Drop-down list of auto-complete choices. Values should be separated with new line'),
                                    'rows' => 5,
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
                ]
            ]
        ];
    }
}