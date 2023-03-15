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

class PhoneNumber extends Text
{
    /**
     * Block's template
     *
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'phone_number.phtml';

    /**
     * @inheritdoc
     */
    public function getFieldClass(): string
    {
        return 'webforms-phone ' . parent::getFieldClass();
    }

    /**
     * @return string
     */
    public function getPreferredCountries(): string
    {
        return json_encode($this->getField()->getPreferredCountries());
    }

    /**
     * @return string
     */
    public function getOnlyCountries(): string
    {
        return json_encode($this->getField()->getOnlyCountries());
    }

    /**
     * @return string
     */
    public function getInitialCountry(): string
    {
        return (string)$this->getField()->getInitialCountry();
    }
}