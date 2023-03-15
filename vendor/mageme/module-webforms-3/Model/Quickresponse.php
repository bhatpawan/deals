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

namespace MageMe\WebForms\Model;

use MageMe\WebForms\Api\Data\QuickresponseInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Quickresponse extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, QuickresponseInterface
{
    /**
     * Quickresponse cache tag
     */
    const CACHE_TAG = 'webforms_quickresponse';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_quickresponse';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_quickresponse';

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

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
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
    public function getQuickresponseCategoryId(): ?int
    {
        return $this->getData(self::QUICKRESPONSE_CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuickresponseCategoryId(int $quickresponseCategoryId): QuickresponseInterface
    {
        return $this->setData(self::QUICKRESPONSE_CATEGORY_ID, $quickresponseCategoryId);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle(?string $title): QuickresponseInterface
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): ?string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage(?string $message): QuickresponseInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): QuickresponseInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): QuickresponseInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Quickresponse::class);
    }

    #endregion


}
