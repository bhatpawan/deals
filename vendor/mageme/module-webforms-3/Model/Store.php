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

use MageMe\WebForms\Api\Data\StoreInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Store extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, StoreInterface
{
    /**
     * Store cache tag
     */
    const CACHE_TAG = 'webforms_store';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_store';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_store';

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
    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(int $storeId): StoreInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): ?int
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId): StoreInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType(): ?string
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setEntityType(?string $entityType): StoreInterface
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * @inheritDoc
     */
    public function getStoreDataSerialized(): ?string
    {
        return $this->getData(self::STORE_DATA_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setStoreDataSerialized(?string $storeDataSerialized): StoreInterface
    {
        return $this->setData(self::STORE_DATA_SERIALIZED, $storeDataSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getStoreData()
    {
        return $this->getData(self::STORE_DATA);
    }
#endregion

    /**
     * @inheritDoc
     */
    public function setStoreData($storeData): StoreInterface
    {
        return $this->setData(self::STORE_DATA, $storeData);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Store::class);
    }
}