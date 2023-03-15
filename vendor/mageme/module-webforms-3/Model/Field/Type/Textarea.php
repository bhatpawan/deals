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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Config\Options\Field\CaseTransform;
use MageMe\WebForms\Model\Field\AbstractField;
use Magento\Framework\DataObject;

class Textarea extends AbstractField
{

    const TYPE_NAME = 'textarea';

    /**
     * Attributes
     */
    const TEXT = 'text';
    const PLACEHOLDER = 'placeholder';
    const CUSTOMER_DATA = 'customer_data';
    const MASK = 'mask';
    const ROWS = 'rows';
    const CASE_TRANSFORM = 'case_transform';

    #region type attributes

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
    public function setText(string $text): Textarea
    {
        return $this->setData(self::TEXT, $text);
    }

    /**
     * Get field placeholder
     *
     * @return string
     */
    public function getPlaceholder(): string
    {
        return (string)$this->getData(self::PLACEHOLDER);
    }

    /**
     * Set field placeholder
     *
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder(string $placeholder): Textarea
    {
        return $this->setData(self::PLACEHOLDER, $placeholder);
    }

    /**
     * Get field customerData
     *
     * @return string
     */
    public function getCustomerData(): string
    {
        return (string)$this->getData(self::CUSTOMER_DATA);
    }

    /**
     * Set field customerData
     *
     * @param string $customerData
     * @return $this
     */
    public function setCustomerData(string $customerData): Textarea
    {
        return $this->setData(self::CUSTOMER_DATA, $customerData);
    }

    /**
     * Get field mask
     *
     * @return string
     */
    public function getMask(): string
    {
        return (string)$this->getData(self::MASK);
    }

    /**
     * Set field mask
     *
     * @param string $mask
     * @return $this
     */
    public function setMask(string $mask): Textarea
    {
        return $this->setData(self::MASK, $mask);
    }

    /**
     * Get rows
     *
     * @return int
     */
    public function getRows(): int
    {
        return (int)$this->getData(self::ROWS);
    }

    /**
     * Set rows
     *
     * @param int $rows
     * @return $this
     */
    public function setRows(int $rows): Textarea
    {
        return $this->setData(self::ROWS, $rows);
    }

    /**
     * Get field case transform
     *
     * @return string
     */
    public function getCaseTransform(): string
    {
        return (string)$this->getData(self::CASE_TRANSFORM);
    }

    /**
     * Set field case transform
     *
     * @param string $caseTransform
     * @return $this
     */
    public function setCaseTransform(string $caseTransform): Textarea
    {
        return $this->setData(self::CASE_TRANSFORM, $caseTransform);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation = parent::getValidation();
        if ($this->getMask()) {
            $validation['rules']['validate-input-mask-complete'] = "'validate-input-mask-complete':true";
        }
        return $validation;
    }

    /**
     * @inheritDoc
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getValueForResultValueRenderer(DataObject $row): string
    {
        $fieldIndex = 'field_' . $this->getId();
        $value      = htmlspecialchars((string)$row->getData($fieldIndex));
        if (strlen($value) <= 200 || mb_substr_count($value, "\n") <= 11) {
            return nl2br($value);
        }
        $div_id  = 'x_' . $this->getId() . '_' . $row->getId();
        $onclick = "$('$div_id').style.display ='block'; this.style.display='none';  return false;";
        $pos     = @strpos($value, "\n", 200);
        if ($pos > 300 || !$pos) {
            $pos = @strpos($value, " ", 200);
        }
        if ($pos > 300) {
            $pos = 200;
        }
        if (!$pos) {
            $pos = 200;
        }
        $html = '<div>' . nl2br(mb_substr($value, 0, $pos)) . '</div>';
        $html .= '<div id="' . $div_id . '" style="display:none">' . nl2br(mb_substr($value, $pos,
                strlen($value))) . '<br></div>';
        $html .= '<a onclick="' . $onclick . '" style="text-decoration:none;float:right">[' . __('Read more') . ']</a>';
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return nl2br(htmlentities((string)$value));
    }

    /**
     * @inheritDoc
     */
    public function preparePostData(
        array           &$postData,
        array           $config = [],
        ResultInterface $result = null,
        bool            $isAdmin = false
    ): FieldInterface {
        $postData['field'][$this->getId()] = trim($postData['field'][$this->getId()]);
        switch ($this->getCaseTransform()) {
            case CaseTransform::LOWER:
            {
                $postData['field'][$this->getId()] = strtolower($postData['field'][$this->getId()]);
                break;
            }
            case CaseTransform::UPPER:
            {
                $postData['field'][$this->getId()] = strtoupper($postData['field'][$this->getId()]);
                break;
            }
        }
        return parent::preparePostData($postData, $config, $result, $isAdmin);
    }
}
