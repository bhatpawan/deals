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

/** @noinspection PhpMissingFieldTypeInspection */


namespace MageMe\WebForms\Model;


use MageMe\WebForms\Api\Data\ResultValueInterface;
use Magento\Framework\DataObject\IdentityInterface;

class ResultValue extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, ResultValueInterface
{

    /**
     * Message cache tag
     */
    const CACHE_TAG = 'webforms_result_value';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_result_value';

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

#region DB getters and setters

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getResultId(): ?int
    {
        return $this->getData(self::RESULT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setResultId(int $resultId): ResultValueInterface
    {
        return $this->setData(self::RESULT_ID, $resultId);
    }

    /**
     * @inheritDoc
     */
    public function getFieldId(): ?int
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setFieldId(int $fieldId): ResultValueInterface
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value): ResultValueInterface
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\ResultValue::class);
    }

#endregion
}