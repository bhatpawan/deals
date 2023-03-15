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
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Listing\Columns\Column;

class Subscribe extends Text
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
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Newsletter Subscription Checkbox Label'),
                                    'additionalInfo' => __('Overwrite default text &quot;Sign Up for Newsletter&quot;<br>Use <i>^Sign Up for Newsletter</i> to check by default'),
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
        $config              = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['filter']    = Filter::SELECT;
        $config['options']   = $this->getField()->getOptionsArray();
        $config['component'] = Component::SELECT;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config           = $this->getDefaultResultAdminFormConfig();
        $config['type']   = 'select';
        $config['values'] = $this->getField()->toOptionArray();
        return $config;
    }
}
