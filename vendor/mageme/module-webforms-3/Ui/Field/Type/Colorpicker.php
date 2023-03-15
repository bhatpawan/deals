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
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;
use Magento\Ui\Component\Form;

class Colorpicker extends Text
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
                                    'formElement' => Form\Element\ColorPicker::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Color'),
                                    'colorFormat' => 'hex',
                                    'colorPickerMode' => 'full'
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
        $config             = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['bodyTmpl'] = BodyTmpl::HTML;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config          = $this->getDefaultResultAdminFormConfig();
        $config['type']  = \MageMe\WebForms\Block\Adminhtml\Result\Element\Colorpicker::TYPE;
        $config['value'] = $this->getField()->getValue();
        return $config;
    }
}
