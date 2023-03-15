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
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\DataType;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Listing\Columns\Column;

class Country extends Select implements FieldResultListingColumnInterface
{
    const DEFAULT_COUNTRY = Type\Country::DEFAULT_COUNTRY;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::DEFAULT_COUNTRY => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::DEFAULT_COUNTRY,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Default Country'),
                                    'options' => $this->getField()->toOptionArray(),
                                ]
                            ]
                        ]
                    ],
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
        $config['dataType']  = DataType::SELECT;
        $config['filter']    = Filter::SELECT;
        $config['options']   = $this->getField()->toOptionArray(' ');
        $config['component'] = Component::SELECT;
        return $config;
    }
}
