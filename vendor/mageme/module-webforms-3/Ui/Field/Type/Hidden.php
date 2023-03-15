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


use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use Magento\Ui\Component\Form;

class Hidden extends Text implements FieldResultListingColumnInterface
{

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
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
                                    'label' => __('Hidden Field Value'),
                                    'additionalInfo' => __("You can use variables to store dynamic information. Example:<br><i>{{var product.sku}}<br>{{var category.name}}<br>{{var customer.email}}<br>{{var url}}</i>"),
                                    'rows' => 5,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}