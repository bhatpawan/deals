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
use MageMe\WebForms\Model\Field\Type;

class PhoneNumber extends Text
{
    const PREFERRED_COUNTRIES = Type\PhoneNumber::PREFERRED_COUNTRIES;
    const ONLY_COUNTRIES = Type\PhoneNumber::ONLY_COUNTRIES;
    const INITIAL_COUNTRY = Type\PhoneNumber::INITIAL_COUNTRY;

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
                    $prefix . '_' . static::PREFERRED_COUNTRIES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\MultiSelect::NAME,
                                    'dataScope' => static::PREFERRED_COUNTRIES,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Preferred Countries'),
                                    'additionalInfo' => __('Display selected countries at the top of the list.'),
                                    'options' => $this->getField()->getCountriesAsOptions(),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::ONLY_COUNTRIES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\MultiSelect::NAME,
                                    'dataScope' => static::ONLY_COUNTRIES,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Limit Country Selection'),
                                    'additionalInfo' => __('Display only specified countries in the dropdown. Unselect for all countries.'),
                                    'options' => $this->getField()->getCountriesAsOptions(),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::INITIAL_COUNTRY => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::INITIAL_COUNTRY,
                                    'visible' => 0,
                                    'sortOrder' => 69,
                                    'label' => __('Initial Country'),
                                    'additionalInfo' => __('Select the default country on form initialization. Set to <i>Auto</i> for automatic country detection by IP address.'),
                                    'options' => $this->getField()->getCountriesAsOptions(),
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}