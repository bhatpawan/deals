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


class Select extends AbstractOption
{

    /**
     * Attributes
     */
    const IS_MULTISELECT = 'is_multiselect';

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation = parent::getValidation();
        if ($this->getIsRequired()) {
            unset($validation['rules']['required-entry']);
            $validation['rules']['validate-select'] = "'validate-select':true";
            if ($this->getValidationRequiredMessage()) {
                unset($validation['descriptions']['data-msg-required-entry']);
                $validation['descriptions']['data-msg-validate-select'] = $this->getValidationRequiredMessage();
            }
        }
        return $validation;
    }

    /**
     * Set multiselect flag
     *
     * @param bool $isMultiselect
     * @return $this
     */
    public function setIsMultiselect(bool $isMultiselect): Select
    {
        return $this->setData(self::IS_MULTISELECT, $isMultiselect);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        $options = $this->getSelectOptions();
        $values = is_array($value) ? $value : explode("\n", (string)$value);
        $arr = [];
        foreach ($values as $item) {
            if (!empty($options[$item])) {
                $arr[] = $item;
            }
        }
        return implode("\n", $arr);
    }

    /**
     * @inheritDoc
     */
    public function getResultCollectionFilterCondition($value, string $prefix = '%'): string
    {
        $id          = $this->getId();
        $searchValue = $this->getResultCollectionFilterConditionSearchValue($value);
        return "results_values_$id.value like '" . $searchValue . "'";
    }

    /**
     * Get multiselect flag
     *
     * @return bool
     */
    public function getIsMultiselect(): bool
    {
        return (bool)$this->getData(self::IS_MULTISELECT);
    }
}
