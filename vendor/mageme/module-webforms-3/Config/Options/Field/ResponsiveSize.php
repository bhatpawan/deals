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

class ResponsiveSize implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('1/1'),
                'value' => "1-1"
            ],
            [
                'label' => __('1/2'),
                'value' => "1-2"
            ],
            [
                'label' => __('1/3'),
                'value' => "1-3"
            ],
            [
                'label' => __('1/4'),
                'value' => "1-4"
            ],
            [
                'label' => __('1/5'),
                'value' => "1-5"
            ],
            [
                'label' => __('1/6'),
                'value' => "1-6"
            ],
            [
                'label' => __('2/3'),
                'value' => "2-3"
            ],
            [
                'label' => __('3/4'),
                'value' => "3-4"
            ],
            [
                'label' => __('2/5'),
                'value' => "2-5"
            ],
            [
                'label' => __('3/5'),
                'value' => "3-5"
            ],
            [
                'label' => __('4/5'),
                'value' => "4-5"
            ],
            [
                'label' => __('5/6'),
                'value' => "5-6"
            ],
        ];
    }
}
