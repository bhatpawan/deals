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
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use Magento\Ui\Component\Form;

class SelectRadio extends Select
{
    const IS_INTERNAL_ELEMENTS_INLINE = Type\SelectRadio::IS_INTERNAL_ELEMENTS_INLINE;

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
                                    'sortOrder' => 66,
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

    public function getResultListingColumnConfig(int $sortOrder): array
    {
        $config            = $this->getDefaultUIResultColumnConfig($sortOrder);
        if(!$this->scopeConfig->getValue('webforms/general/show_select_db_value')) {
            $config['filter']    = Filter::SELECT;
            $config['options']   = $this->getField()->toOptionArray();
            $config['component'] = Component::SELECT;
        }
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config             = $this->getDefaultResultAdminFormConfig();
        $config['type']     = 'select';
        $config['required'] = false;
        $config['values']   = $this->getField()->getOptionsArray();
        return $config;
    }
}