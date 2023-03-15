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

namespace MageMe\WebForms\Config\Options\Logic;

use Magento\Framework\Data\OptionSourceInterface;

class Condition implements OptionSourceInterface
{
    const CONDITION_EQUAL = 'equal';
    const CONDITION_NOTEQUAL = 'notequal';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::CONDITION_EQUAL, 'label' => __('Equal')],
            ['value' => self::CONDITION_NOTEQUAL, 'label' => __('NOT equal')],
        ];
    }
}