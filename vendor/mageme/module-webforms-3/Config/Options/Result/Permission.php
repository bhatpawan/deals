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

namespace MageMe\WebForms\Config\Options\Result;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Permission
 * @package MageMe\WebForms\Config\Options\Result
 */
class Permission implements OptionSourceInterface
{
    const ADD = 'add';
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::ADD, 'label' => __('Add')],
            ['value' => self::VIEW, 'label' => __('View')],
            ['value' => self::EDIT, 'label' => __('Edit')],
            ['value' => self::DELETE, 'label' => __('Delete')],
        ];
    }
}
