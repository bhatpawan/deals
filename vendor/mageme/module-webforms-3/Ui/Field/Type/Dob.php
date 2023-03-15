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


use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Ui\Component\Form;

class Dob extends Date
{
    const IS_FILLED_BY_CUSTOMER_DOB = Type\Dob::IS_FILLED_BY_CUSTOMER_DOB;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::IS_FILLED_BY_CUSTOMER_DOB => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_FILLED_BY_CUSTOMER_DOB,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Pre-fill With Customer Data'),
                                    'additionalInfo' => __('Use customer date of birth account data to pre-fill the field'),
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
}
