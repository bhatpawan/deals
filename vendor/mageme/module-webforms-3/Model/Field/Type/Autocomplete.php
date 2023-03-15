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


class Autocomplete extends Text
{

    const TYPE_NAME = 'autocomplete';

    /**
     * Get autocomplete choices
     *
     * @return array
     */
    public function getChoices(): array
    {
        return explode("\n", $this->getText());
    }

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $customer_value = $this->getCustomerValue();
        return $customer_value ?: '';
    }

}
