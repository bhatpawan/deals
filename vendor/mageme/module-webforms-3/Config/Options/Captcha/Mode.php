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

namespace MageMe\WebForms\Config\Options\Captcha;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    const MODE_DEFAULT = 'default';

    const MODE_AUTO = 'auto';

    const MODE_ALWAYS = 'always';

    const MODE_OFF = 'off';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Default'),
                'value' => self::MODE_DEFAULT
            ],
            [
                'label' => __('Auto (hidden for logged in customers)'),
                'value' => self::MODE_AUTO
            ],
            [
                'label' => __('Always on'),
                'value' => self::MODE_ALWAYS
            ],
            [
                'label' => __('Off'),
                'value' => self::MODE_OFF
            ],
        ];
    }
}
