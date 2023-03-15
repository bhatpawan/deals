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


class SelectRadio extends AbstractOption
{
    /**
     * Attributes
     */
    const IS_INTERNAL_ELEMENTS_INLINE = 'is_internal_elements_inline';

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation = parent::getValidation();
        if ($this->getIsRequired()) {
            unset($validation['rules']['required-entry']);
            $validation['rules']['validate-one-required-by-name'] = "'validate-one-required-by-name':true";
            if ($this->getValidationRequiredMessage()) {
                unset($validation['descriptions']['data-msg-required-entry']);
                $validation['descriptions']['data-msg-validate-one-required-by-name'] = $this->getValidationRequiredMessage();
            }
        }
        return $validation;
    }


    #region type attributes

    /**
     * Set if elements of the field such as radio or checkboxes inline instead of the column
     *
     * @param bool $isInternalElementsInline
     * @return $this
     */
    public function setIsInternalElementsInline(bool $isInternalElementsInline): SelectRadio
    {
        return $this->setData(self::IS_INTERNAL_ELEMENTS_INLINE, $isInternalElementsInline);
    }

    /**
     * @inheritDoc
     */
    public function getCssContainerClass(): ?string
    {
        $class = parent::getCssContainerClass();
        return $this->getIsInternalElementsInline() ? 'inline-elements ' . $class : $class;
    }
    #endregion

    /**
     * Get if elements of the field such as radio or checkboxes inline instead of the column
     *
     * @return bool
     */
    public function getIsInternalElementsInline(): bool
    {
        return (bool)$this->getData(self::IS_INTERNAL_ELEMENTS_INLINE);
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
     * @inheritDoc
     */
    public function getLabelForForFormDefaultTemplate(string $uid): string
    {
        return '';
    }
}
