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


class Select extends AbstractOption
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'select.phtml';

    /**
     * @inheritDoc
     */
    public function getFieldName(): string
    {
        $name = parent::getFieldName();
        if ($this->getIsMultiselect()) {
            $name .= '[]';
        }
        return $name;
    }

    /**
     * @return bool
     */
    public function getIsMultiselect(): bool
    {
        return $this->field->getIsMultiselect();
    }

    /**
     * @inheritDoc
     */
    public function getFieldClass(): string
    {
        $class = parent::getFieldClass();
        if ($this->getIsMultiselect()) {
            $class .= ' multiselect';
        }
        return $class;
    }
}
