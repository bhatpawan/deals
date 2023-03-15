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

namespace MageMe\WebForms\Block\Form\Element\Field\Type;

class Subscribe extends AbstractOption
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'subscribe.phtml';

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        $field = $this->getField();
        $isCheckedByDefault = $field->isCheckedOption($field->getLabel());
        return $field->getCustomerValue() !== false ? (bool)$field->getCustomerValue() : $isCheckedByDefault;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        $label = $this->getField()->getCheckedOptionValue($this->field->getLabel());
        return $this->applyTranslation($label ?: __('Sign Up for Newsletter'));
    }
}
