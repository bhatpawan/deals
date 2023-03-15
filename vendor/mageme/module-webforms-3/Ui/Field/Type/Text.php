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


use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Config\Options\CustomerDataOptions;
use MageMe\WebForms\Config\Options\Field\CaseTransform;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Ui\Component\Form;

class Text extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const TEXT = Type\Text::TEXT;
    const PLACEHOLDER = Type\Text::PLACEHOLDER;
    const CUSTOMER_DATA = Type\Text::CUSTOMER_DATA;
    const MASK = Type\Text::MASK;
    const CASE_TRANSFORM = Type\Text::CASE_TRANSFORM;

    /**
     * @var CustomerDataOptions
     */
    protected $customerDataOptions;
    /**
     * @var CaseTransform
     */
    protected $caseTransform;

    /**
     * Text constructor.
     *
     * @param CaseTransform $caseTransform
     * @param CustomerDataOptions $customerDataOptions
     */
    public function __construct(
        CaseTransform $caseTransform,
        CustomerDataOptions $customerDataOptions
    )
    {
        $this->customerDataOptions = $customerDataOptions;
        $this->caseTransform = $caseTransform;
    }

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
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Default Field Value'),
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
                ]
            ]
        ];
    }
}