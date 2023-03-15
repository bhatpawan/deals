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


class Url extends Text
{
    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation                          = parent::getValidation();
        $validation['rules']['validate-url'] = "'validate-url':true";
        return $validation;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        if (!is_string($value)) {
            return '';
        }
        $value = htmlentities((string)$value);
        return empty($value) ? '' : '<a href="' . $value . '">' . $value . '</a>';
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return $this->getValueForResultHtml($value, $options);
    }
}
