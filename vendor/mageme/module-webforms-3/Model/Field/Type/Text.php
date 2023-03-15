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

class Text extends AbstractField
{
    /**
     * Attributes
     */
    const TEXT = 'text';
    const PLACEHOLDER = 'placeholder';
    const CUSTOMER_DATA = 'customer_data';
    const MASK = 'mask';
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
    public function setText(string $text): Text
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
    public function setPlaceholder(string $placeholder): Text
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
    public function setCustomerData(string $customerData): Text
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
    public function setMask(string $mask): Text
    {
        return $this->setData(self::MASK, $mask);
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
    public function setCaseTransform(string $caseTransform): Text
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
