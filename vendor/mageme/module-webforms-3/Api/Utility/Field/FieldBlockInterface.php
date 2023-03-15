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

namespace MageMe\WebForms\Api\Utility\Field;


use MageMe\WebForms\Api\Data\FieldInterface;
use Magento\Framework\View\Element\BlockInterface;

interface FieldBlockInterface extends BlockInterface
{
    /**
     * Set field model for block
     *
     * @param FieldInterface $field
     * @return FieldBlockInterface
     */
    public function setField(FieldInterface $field): FieldBlockInterface;

    /**
     * Get block's field model
     *
     * @return FieldInterface
     */
    public function getField(): FieldInterface;
}
