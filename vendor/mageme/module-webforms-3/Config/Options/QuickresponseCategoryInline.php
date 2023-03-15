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

class QuickresponseCategoryInline extends QuickresponseCategory
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options    = [
            [
                'label' => ' ',
                'value' => null,
            ]
        ];
        return array_merge($options, parent::toOptionArray());
    }
}