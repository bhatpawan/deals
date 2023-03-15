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

class Dob extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'dob.phtml';

    /**
     * @var array
     */
    protected $_dateInputs = [];

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->getFieldValue() ? date('d', $this->getFieldValue()) : '';
    }

    /**
     * @return string
     */
    public function getMonth(): string
    {
        return $this->getFieldValue() ? date('m', $this->getFieldValue()) : '';
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->getFieldValue() ? date('Y', $this->getFieldValue()) : '';
    }

    /**
     * Add date input html
     *
     * @param string $code
     * @param string $html
     */
    public function setDateInput(string $code, string $html)
    {
        $this->_dateInputs[$code] = $html;
    }

    /**
     * Sort date inputs by dateformat order of current locale
     *
     * @return string
     */
    public function getSortedDateInputs(): string
    {
        $strtr = [
            '%b' => '%1$s',
            '%B' => '%1$s',
            '%m' => '%1$s',
            '%d' => '%2$s',
            '%e' => '%2$s',
            '%Y' => '%3$s',
            '%y' => '%3$s',
            'Y' => '%3$s',
            'M' =>  '%1$s',
            'd' =>  '%2$s',
        ];

        $dateFormat = preg_replace('/[^%\w]/', '\\1', $this->getDateFormat());

        return sprintf(strtr($dateFormat, $strtr),
            $this->_dateInputs['m'], $this->_dateInputs['d'], $this->_dateInputs['y']);
    }

    /**
     * Returns format which will be applied for DOB in javascript
     *
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->getField()->getDateFormat();
    }
}
