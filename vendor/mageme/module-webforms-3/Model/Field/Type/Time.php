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

namespace MageMe\WebForms\Model\Field\Type;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Field\AbstractField;

class Time extends AbstractField
{
    /**
     * Attributes
     */
    const AVAILABLE_HOURS = 'available_hours';
    const AVAILABLE_MINUTES = 'available_minutes';

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $customer_value = $this->getCustomerValue();
        if (is_string($customer_value) && !empty($customer_value)) {
            return explode(":", $customer_value);
        }
        return parent::getFilteredFieldValue();
    }

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation                                  = parent::getValidation();
        $validation['rules']['time']                 = "'time':true";
        $validation['descriptions']['data-msg-time'] = __('Please select a valid time');
        return $validation;
    }

    /**
     * @return array
     */
    public function getHoursOptions(): array
    {
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hour    = sprintf("%02d", $i);
            $hours[] = [
                'label' => $hour,
                'value' => $hour
            ];
        }
        return $hours;
    }

    /**
     * @return array
     */
    public function getMinutesOptions(): array
    {
        $minutes = [];
        for ($i = 0; $i < 60; $i += 5) {
            $minute    = sprintf("%02d", $i);
            $minutes[] = [
                'label' => $minute,
                'value' => $minute
            ];
        }
        return $minutes;
    }

    #region type attributes

    /**
     * @return array
     */
    public function getAvailableHours(): array
    {
        return is_array($this->getData(self::AVAILABLE_HOURS)) ? $this->getData(self::AVAILABLE_HOURS) : [];
    }

    /**
     * @param array $availableHours
     * @return $this
     */
    public function setAvailableHours(array $availableHours): Time
    {
        return $this->setData(self::AVAILABLE_HOURS, $availableHours);
    }

    /**
     * @return array
     */
    public function getAvailableMinutes(): array
    {
        return is_array($this->getData(self::AVAILABLE_MINUTES)) ? $this->getData(self::AVAILABLE_MINUTES) : [];
    }

    /**
     * @param array $availableMinutes
     * @return $this
     */
    public function setAvailableMinutes(array $availableMinutes): Time
    {
        return $this->setData(self::AVAILABLE_MINUTES, $availableMinutes);
    }
#endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        return is_array($value) ? implode(":", $value) : $value;
    }
}