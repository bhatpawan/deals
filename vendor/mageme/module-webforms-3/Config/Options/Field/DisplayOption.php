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

namespace MageMe\WebForms\Config\Options\Field;


use Magento\Framework\Data\OptionSourceInterface;

class DisplayOption implements OptionSourceInterface
{
    const OPTION_ON = 'on';
    const OPTION_OFF = 'off';
    const OPTION_VALUE = 'value';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('On'),
                'value' => static::OPTION_ON,
            ],
            [
                'label' => __('Off'),
                'value' => static::OPTION_OFF,
            ],
            [
                'label' => __('Value only'),
                'value' => static::OPTION_VALUE,
            ],
        ];
    }
}
