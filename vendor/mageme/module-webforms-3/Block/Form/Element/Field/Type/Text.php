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


use MageMe\WebForms\Block\Form\Element\Field\AbstractField;

class Text extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'text.phtml';

    public function getFieldClass(): string
    {
        return 'input-text ' . parent::getFieldClass();
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return htmlspecialchars((string)$this->applyTranslation(trim((string)$this->field->getPlaceholder() ?? '')));
    }

    /**
     * @return string
     */
    public function getCustomerData(): string
    {
        return $this->getField()->getCustomerData();
    }

    /**
     * @return string
     */
    public function getMask(): string
    {
        return (string)$this->getField()->getMask();
    }
}
