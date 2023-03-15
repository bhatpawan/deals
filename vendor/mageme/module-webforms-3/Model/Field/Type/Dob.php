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


class Dob extends Date
{

    /**
     * Attributes
     */
    const IS_FILLED_BY_CUSTOMER_DOB = 'is_filled_by_customer_dob';

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $customer_value = $this->getCustomerValue();
        if ($customer_value) {
            return strtotime($customer_value);
        }
        if ($this->getIsFilledByCustomerDob() && $this->getCustomer()) {

            /** @noinspection PhpUndefinedMethodInspection */
            return strtotime($this->getCustomer()->getDob());
        }
        return false;
    }

    #region type attributes

    /**
     * Get filled by customer data flag
     *
     * @return bool
     */
    public function getIsFilledByCustomerDob(): bool
    {
        return (bool)$this->getData(self::IS_FILLED_BY_CUSTOMER_DOB);
    }


    /**
     * Set filled by customer data flag
     *
     * @param bool $isFilledByCustomerDob
     * @return $this
     */
    public function setIsFilledByCustomerDob(bool $isFilledByCustomerDob): Dob
    {
        return $this->setData(self::IS_FILLED_BY_CUSTOMER_DOB, $isFilledByCustomerDob);
    }
    #endregion

    /**
     * Get date format
     *
     * @return string
     */
    public function getDateStrFormat(): string
    {
        return $this->dateHelper->convertZendToStrftime($this->getFormat());
    }
}
