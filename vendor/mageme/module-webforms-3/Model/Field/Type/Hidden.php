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


use Exception;
use MageMe\WebForms\Api\Utility\Field\HiddenInterface;
use MageMe\WebForms\Model\Field\AbstractField;

class Hidden extends AbstractField implements HiddenInterface
{

    const TYPE_NAME = 'hidden';

    /**
     * Attributes
     */
    const TEXT = 'text';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getFilteredFieldValue()
    {
        $customerValue = $this->getCustomerValue();
        if ($customerValue) {
            return $customerValue;
        }

        $fieldValue = $this->getText();
        return $fieldValue ? trim((string)filter_var($fieldValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '';
    }

    /**
     * Get field text
     *
     * @return string
     */
    public function getText(): string
    {
        return (string)$this->getData(self::TEXT);
    }

    /**
     * Set field text
     *
     * @param string $text
     * @return $this
     */
    public function setText(string $text): Hidden
    {
        return $this->setData(self::TEXT, $text);
    }

    /**
     * @inheritDoc
     */
    public function validatePostRequired(array $postData, bool $logicVisibility): bool
    {
        return empty($postData[$this->getId()]);
    }
}
