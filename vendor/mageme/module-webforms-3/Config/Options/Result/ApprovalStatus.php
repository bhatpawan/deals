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

class ApprovalStatus implements OptionSourceInterface
{
    const STATUS_NOT_APPROVED = -1;
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_COMPLETED = 2;

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Not Approved'),
                'value' => self::STATUS_NOT_APPROVED,
            ],
            [
                'label' => __('Pending'),
                'value' => self::STATUS_PENDING,
            ],
            [
                'label' => __('Approved'),
                'value' => self::STATUS_APPROVED,
            ],
            [
                'label' => __('Completed'),
                'value' => self::STATUS_COMPLETED,
            ],
        ];
    }
}
