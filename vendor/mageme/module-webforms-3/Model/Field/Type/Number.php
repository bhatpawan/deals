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


class Number extends Text
{
    /**
     * Attributes
     */
    const MIN = 'min';
    const MAX = 'max';

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation                             = parent::getValidation();
        $validation['rules']['validate-number'] = "'validate-number':true";

        $min = $this->getMin();
        $max = $this->getMax();
        if ($min && $max) {
            $validation['rules']['validate-number-range']                 = "'validate-number-range':'" . $min . '-' . $max . "'";
            $validation['descriptions']['data-msg-validate-number-range'] = __('Please enter a value between %1 and %2.',
                $min, $max);
        } elseif ($min) {
            $validation['rules']['min'] = "'min':'" . $min . "'";
        } elseif ($max) {
            $validation['rules']['max'] = "'max':'" . $max . "'";
        }

        return $validation;
    }

    #region type attributes

    /**
     * Get minimum value for validation
     *
     * @return int
     */
    public function getMin(): int
    {
        return (int)$this->getData(self::MIN);
    }

    /**
     * Get maximum value for validation
     *
     * @return int
     */
    public function getMax(): int
    {
        return (int)$this->getData(self::MAX);
    }

    /**
     * Set minimum value for validation
     *
     * @param int $min
     * @return $this
     */
    public function setMin(int $min): Number
    {
        return $this->setData(self::MIN, $min);
    }

    /**
     * Set maximum value for validation
     *
     * @param int $max
     * @return $this
     */
    public function setMax(int $max): Number
    {
        return $this->setData(self::MAX, $max);
    }
    #endregion
}
