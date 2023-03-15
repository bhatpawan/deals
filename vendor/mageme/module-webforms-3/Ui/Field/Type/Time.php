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
use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Ui\Component\Form;

class Time extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const AVAILABLE_HOURS = Type\Time::AVAILABLE_HOURS;
    const AVAILABLE_MINUTES = Type\Time::AVAILABLE_MINUTES;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::AVAILABLE_HOURS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\MultiSelect::NAME,
                                    'dataScope' => static::AVAILABLE_HOURS,
                                    'visible' => 0,
                                    'sortOrder' => 45,
                                    'label' => __('Available Hours'),
                                    'options' => $this->getField()->getHoursOptions(),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::AVAILABLE_MINUTES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\MultiSelect::NAME,
                                    'dataScope' => static::AVAILABLE_MINUTES,
                                    'visible' => 0,
                                    'sortOrder' => 46,
                                    'label' => __('Available Minutes'),
                                    'options' => $this->getField()->getMinutesOptions(),
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
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config              = $this->getDefaultResultAdminFormConfig();
        $config['type']      = \MageMe\WebForms\Block\Adminhtml\Result\Element\Time::TYPE;
        $config['field_id']  = $this->getField()->getId();
        $config['result_id'] = $result->getId();
        return $config;
    }
}