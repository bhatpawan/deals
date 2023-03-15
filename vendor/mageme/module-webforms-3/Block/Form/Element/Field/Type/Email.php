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


class Email extends Text
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'email.phtml';

    /**
     * @return bool
     */
    public function getIsFilledByCustomerEmail(): bool
    {
        return $this->getField()->getIsFilledByCustomerEmail();
    }

    /**
     * @return string
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getMatchValueFieldId(): string
    {
        if (!$this->getField()->getMatchValueFieldId()) {
            return '';
        }
        return 'field' .$this->getField()->getUid() . $this->getField()->getMatchValueFieldId();
    }
}
