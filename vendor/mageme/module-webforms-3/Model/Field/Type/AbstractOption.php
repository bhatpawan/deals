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


use MageMe\WebForms\Model\Field\AbstractField;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\UrlInterface;

abstract class AbstractOption extends AbstractField implements OptionSourceInterface
{
    const OPTIONS = 'options';

    /**
     * @var string
     */
    protected $img_regex = '/{{img ([\w\/\.-]+)}}/';

    /**
     * @var string
     */
    protected $val_regex = '/{{val (.*?)}}/';

    /**
     * @var string
     */
    protected $optgroup_regex = '/{{optgroup\s*label="(.*?)"}}/i';

    /**
     * @var string
     */
    protected $contact_regex = '/ *\<[^\>]+\> *$/';

    /**
     * Set options
     *
     * @param string $options
     * @return $this
     */
    public function setOptions(string $options): AbstractOption
    {
        return $this->setData(self::OPTIONS, $options);
    }

    /**
     * @return array
     */
    public function getOptionsArray(): array
    {
        $options = [];
        foreach ($this->getOptionsAsArray() as $val) {
            $image_src      = false;
            $optgroup       = false;
            $optgroup_close = false;

            $matches = [];
            preg_match($this->img_regex, (string)$val, $matches);
            if (!empty($matches[1])) {
                $image_src = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $matches[1];
            }

            if (strlen(trim((string)$val)) > 0) {
                $value   = $this->getCheckedOptionValue($val);
                $label   = $value;
                $matches = [];
                preg_match($this->val_regex, $value, $matches);
                if (!empty($matches[1])) {
                    $value = trim((string)$matches[1]);
                }

                preg_match($this->optgroup_regex, $value, $matches);
                if (isset($matches[1])) {
                    $label    = $matches[1];
                    $optgroup = true;
                }

                if (trim($value) == '{{/optgroup}}') {
                    $optgroup_close = true;
                }

                $options[] = [
                    'value' => $this->replaceCodesWithData($value),
                    'label' => trim($this->replaceCodesWithData($label)),
                    'null' => $this->isNullOption($val),
                    'checked' => $this->isCheckedOption($val),
                    'disabled' => $this->isDisabledOption($val),
                    'image_src' => $image_src,
                    'optgroup' => $optgroup,
                    'optgroup_close' => $optgroup_close,
                ];
            }
        }
        return $options;
    }

    /**
     * Get options as array
     *
     * @return array
     */
    protected function getOptionsAsArray(): array
    {
        $options = $this->getOptions();
        if (empty($options)) return [];
        if (is_string($options)) {
            $options = explode("\n", $options);
        }
        if (!is_array($options)) return [];
        return $options;
    }

    /**
     * Get options
     *
     * @return string
     */
    public function getOptions(): string
    {
        return (string)$this->getData(self::OPTIONS);
    }

    /**
     * Get value without extra markup
     *
     * @param string $value
     * @return string
     */
    public function getCheckedOptionValue(string $value): string
    {
        $value = preg_replace($this->img_regex, "", $value);
        $value = str_replace('{{null}}', '', $value);
        $value = str_replace('{{disabled}}', '', $value);

        if ($this->isNullOption($value) && substr($value, 0, 2) == '^^') {
            return trim(substr($value, 2));
        }

        if (substr($value, 0, 1) == '^') {
            return trim(substr($value, 1));
        }

        return trim($value);
    }

    /**
     * Null option doesn't have value
     *
     * @param string $value
     * @return bool
     */
    public function isNullOption(string $value): bool
    {
        if (substr($value, 0, 2) == '^^') {
            return true;
        }
        if (stristr($value, '{{null}}')) {
            return true;
        }
        return false;
    }

    /**
     * Checked option by default
     *
     * @param $value
     * @return bool
     */
    public function isCheckedOption($value): bool
    {
        $customer_value = $this->getCustomerValue();
        if ($customer_value) {

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if ($this->getIsMultiselect()) {
                $customer_value = str_replace(",", "\n", (string)$customer_value);
            }
            $customer_values_array = explode("\n", (string)$customer_value);
            foreach ($customer_values_array as $val) {
                $realVal = $this->getRealCheckedOptionValue($value);
                if (trim($val) == $realVal) {
                    return true;
                }
                $realVal = trim(preg_replace($this->contact_regex, '', $realVal));
                if (trim($val) == $realVal) {
                    return true;
                }
            }
            return false;
        }
        if (substr($value, 0, 1) == '^') {
            return true;
        }
        return false;
    }

    /**
     * Get the value set in the {{val ...}} construction
     *
     * @param $value
     * @return string
     */
    public function getRealCheckedOptionValue($value): string
    {
        $value   = preg_replace($this->img_regex, "", $value ?? '');
        $matches = [];

        preg_match($this->val_regex, (string)$value, $matches);

        if (!empty($matches[1])) {
            $value = trim((string)$matches[1]);
        }

        if ($this->isNullOption($value)) {
            return trim(substr($value, 2));
        }

        if (substr($value, 0, 1) == '^') {
            return trim(substr($value, 1));
        }
        return trim($value);
    }

    /**
     * Disabled option is not possible to select
     *
     * @param string $value
     * @return bool
     */
    public function isDisabledOption(string $value): bool
    {
        if (stristr($value, '{{disabled}}')) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options        = $this->getOptionsAsArray();
        $options        = array_map('trim', $options);
        $select_options = [];
        $optgroup       = false;
        $optgroup_value = [];
        $optgroup_label = "";
        foreach ($options as $o) {
            $value   = $this->getCheckedOptionValue($o);
            $label   = $value;
            $matches = [];

            preg_match($this->val_regex, $value, $matches);
            if (isset($matches[1])) {
                $value = trim((string)$matches[1]);
                $label = preg_replace($this->val_regex, "", $label);
            }

            if ($value == '{{/optgroup}}') {
                $optgroup       = false;
                $value          = $optgroup_value;
                $label          = $optgroup_label;
                $optgroup_value = [];
                $optgroup_label = "";
            }

            if ($optgroup) {
                $optgroup_value[] = ['label' => $label, 'value' => $value];
            }

            if (is_string($value)) {
                preg_match($this->optgroup_regex, $value, $matches);

                if (isset($matches[1])) {
                    $optgroup_label = $matches[1];
                    $optgroup       = true;
                }
            }

            if (!$optgroup) {
                $select_options[] = ['label' => $label, 'value' => $value];
            }
        }
        return $select_options;
    }

    /**
     * Get options tree with groups
     *
     * @return array
     */
    public function getSelectOptions(): array
    {
        $options        = $this->getOptionsAsArray();
        $options        = array_map('trim', $options);
        $select_options = [];
        foreach ($options as $o) {
            $value   = $this->getCheckedOptionValue($o);
            $label   = $value;
            $matches = [];
            preg_match($this->val_regex, $value, $matches);
            if (isset($matches[1])) {
                $value = trim((string)$matches[1]);
                $label = preg_replace($this->val_regex, "", $label);
            }
            preg_match($this->optgroup_regex, $value, $matches);

            if (isset($matches[1])) {
                $label = "== " . $matches[1] . " ==";
                $value = $label;
            }

            if (trim($value) != '{{/optgroup}}') {
                $select_options[$value] = trim((string)$label);
            }
        }
        return $select_options;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        if (is_array($value)) {
            $value = implode("\n", $value);
        }
        return parent::getValueForResultHtml($value, $options);
    }
}
