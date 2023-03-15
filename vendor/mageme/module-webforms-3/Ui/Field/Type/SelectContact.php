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


use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use Magento\Ui\Component\Form;

class SelectContact extends Select
{

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
                                    'additionalInfo' => __('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Options format:<br><i>Site Admin &lt;admin@mysite.com&gt;<br>Sales &lt;sales@mysite.com&gt;</i><br>Use <i>{{optgroup label="Option group"}}...{{/optgroup}}</i> to create group of options<br>Example:<br><i>{{optgroup label="Fruits"}}<br>Apple<br>Banana<br>Mango<br>{{/optgroup}}</i>'),
                                    'rows' => 5,
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
        $config            = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['filter']    = Filter::SELECT;
        $config['options']   = $this->getField()->toOptionArray();
        return $config;
    }
}