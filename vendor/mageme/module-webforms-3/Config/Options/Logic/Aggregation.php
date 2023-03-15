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

class Aggregation implements OptionSourceInterface
{
    const AGGREGATION_ANY = 'any';
    const AGGREGATION_ALL = 'all';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::AGGREGATION_ANY, 'label' => __('Any value can be checked')],
            ['value' => self::AGGREGATION_ALL, 'label' => __('All values should be checked')]
        ];
    }
}
