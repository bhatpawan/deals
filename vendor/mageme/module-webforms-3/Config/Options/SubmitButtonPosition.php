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

namespace MageMe\WebForms\Config\Options;

use Magento\Framework\Data\OptionSourceInterface;

class SubmitButtonPosition implements OptionSourceInterface
{
    const LEFT = '';
    const RIGHT = 'right';
    const CENTER = 'center';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Left'),
                'value' => self::LEFT,
            ],
            [
                'label' => __('Right'),
                'value' => self::RIGHT,
            ],
            [
                'label' => __('Center'),
                'value' => self::CENTER,
            ],
        ];
    }
}