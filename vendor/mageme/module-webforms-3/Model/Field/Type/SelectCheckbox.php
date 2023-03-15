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


class SelectCheckbox extends AbstractOption
{

    /**
     * Attributes
     */
    const MIN_OPTIONS = 'min_options';
    const MAX_OPTIONS = 'max_options';
    const MIN_OPTIONS_ERROR_TEXT = 'min_options_error_text';
    const MAX_OPTIONS_ERROR_TEXT = 'max_options_error_text';
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
        if ($this->getMinOptions()) {
            $validation['rules']['validate-options-checkbox-min'] = "'validate-options-checkbox-min':'" . $this->getMinOptions() . "'";
            if ($this->getMinOptionsErrorText()) {
                $validation['descriptions']['data-msg-validate-options-checkbox-min'] = $this->getMinOptionsErrorText();
            }
        }
        if ($this->getMaxOptions()) {
            $validation['rules']['validate-options-checkbox-max'] = "'validate-options-checkbox-max':'" . $this->getMaxOptions() . "'";
            if ($this->getMaxOptionsErrorText()) {
                $validation['descriptions']['data-msg-validate-options-checkbox-max'] = $this->getMaxOptionsErrorText();
            }
        }

        return $validation;
    }

    #region type attributes

    /**
     * Get minimum selected options for validation
     *
     * @return int
     */
    public function getMinOptions(): int
    {
        return (int)$this->getData(self::MIN_OPTIONS);
    }

    /**
     * Get minimum selected options error text for validation
     *
     * @return string
     */
    public function getMinOptionsErrorText(): string
    {
        return (string)$this->getData(self::MIN_OPTIONS_ERROR_TEXT);
    }

    /**
     * Get maximum selected options for validation
     *
     * @return int
     */
    public function getMaxOptions(): int
    {
        return (int)$this->getData(self::MAX_OPTIONS);
    }

    /**
     * Get maximum selected options error text for validation
     *
     * @return string
     */
    public function getMaxOptionsErrorText(): string
    {
        return (string)$this->getData(self::MAX_OPTIONS_ERROR_TEXT);
    }

    /**
     * Set minimum selected options for validation
     *
     * @param int $minOptions
     * @return $this
     */
    public function setMinOptions(int $minOptions): SelectCheckbox
    {
        return $this->setData(self::MIN_OPTIONS, $minOptions);
    }

    /**
     * Set maximum selected options for validation
     *
     * @param int $maxOptions
     * @return $this
     */
    public function setMaxOptions(int $maxOptions): SelectCheckbox
    {
        return $this->setData(self::MAX_OPTIONS, $maxOptions);
    }

    /**
     * Set minimum selected options error text for validation
     *
     * @param string $minOptionsErrorText
     * @return $this
     */
    public function setMinOptionsErrorText(string $minOptionsErrorText): SelectCheckbox
    {
        return $this->setData(self::MIN_OPTIONS_ERROR_TEXT, $minOptionsErrorText);
    }

    /**
     * Set maximum selected options error text for validation
     *
     * @param string $maxOptionsErrorText
     * @return $this
     */
    public function setMaxOptionsErrorText(string $maxOptionsErrorText): SelectCheckbox
    {
        return $this->setData(self::MAX_OPTIONS_ERROR_TEXT, $maxOptionsErrorText);
    }

    /**
     * Set if elements of the field such as radio or checkboxes inline instead of the column
     *
     * @param bool $isInternalElementsInline
     * @return $this
     */
    public function setIsInternalElementsInline(bool $isInternalElementsInline): SelectCheckbox
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
     * Get multiselect flag
     *
     * @return bool
     */
    public function getIsMultiselect(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array
    {
        $errors = parent::getPostErrors($postData, $logicVisibility);

        // check min options
        if ($this->getMinOptions()) {
            if (!$this->_validatePostMinOptions($postData)) {
                $errorMsg = $this->getMinOptionsErrorText();
                $errors[] = $errorMsg ? $errorMsg : __('Please check at least %1 options', $this->getMinOptions());
            }
        }

        // check max options
        if ($this->getMaxOptions()) {
            if (!$this->_validatePostMaxOptions($postData)) {
                $errorMsg = $this->getMaxOptionsErrorText();
                $errors[] = $errorMsg ? $errorMsg : __('Please check not more than %1', $this->getMaxOptions());
            }
        }
        return $errors;
    }

    /**
     * Check minimum required options
     *
     * @param array $postData
     * @return bool
     */
    protected function _validatePostMinOptions(array $postData): bool
    {
        $fields = $postData['field'];
        if (!empty($fields[$this->getId()])) {
            $count = count($fields[$this->getId()]);
            return !($count > 0 && $count < $this->getMinOptions());
        }
        return true;
    }

    /**
     * Check maximum options
     *
     * @param array $postData
     * @return bool
     */
    protected function _validatePostMaxOptions(array $postData): bool
    {
        $fields = $postData['field'];
        if (!empty($fields[$this->getId()])) {
            $count = count($fields[$this->getId()]);
            return !($this->getMaxOptions() > 0 && $count > $this->getMaxOptions());
        }
        return true;
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
    public function getLabelForForFormDefaultTemplate(string $uid): string
    {
        return '';
    }

    public function convertRawValue($value, array $config = [])
    {
        return is_array($value) ? $value : explode("\n", (string)$value);
    }
}
