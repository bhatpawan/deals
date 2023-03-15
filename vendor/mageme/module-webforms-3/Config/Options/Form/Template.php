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

namespace MageMe\WebForms\Config\Options\Form;

use Magento\Framework\Data\OptionSourceInterface;

class Template implements OptionSourceInterface
{
    const DEFAULT = 'form/default.phtml';
    const MULTISTEP = 'form/multistep.phtml';
    const SIDEBAR = 'form/sidebar.phtml';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Default'),
                'value' => self::DEFAULT,
            ],
            [
                'label' => __('Multistep (display fieldsets as steps)'),
                'value' => self::MULTISTEP,
            ],
            [
                'label' => __('Sidebar (compact sidebar block)'),
                'value' => self::SIDEBAR,
            ]
        ];
    }
}
