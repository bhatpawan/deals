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

namespace MageMe\WebForms\Api\Data;


interface StoreInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const STORE_ID = 'store_id';
    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'entity_type';
    const STORE_DATA_SERIALIZED = 'store_data_serialized';
    /**#@-*/

    /**#@+
     * Additional constants for keys of data array.
     */
    const STORE_DATA = 'store_data';
    /**#@-*/

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set id
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get storeId
     *
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * Set storeId
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): StoreInterface;

    /**
     * Get entityId
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set entityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId(int $entityId): StoreInterface;

    /**
     * Get entityType
     *
     * @return string|null
     */
    public function getEntityType(): ?string;

    /**
     * Set entityType
     *
     * @param string|null $entityType
     * @return $this
     */
    public function setEntityType(?string $entityType): StoreInterface;

    /**
     * Get storeData serialized
     *
     * @return string|null
     */
    public function getStoreDataSerialized(): ?string;

    /**
     * Set storeData serialized
     *
     * @param string|null $storeDataSerialized
     * @return $this
     */
    public function setStoreDataSerialized(?string $storeDataSerialized): StoreInterface;

    /**
     * Get storeData
     *
     * @return mixed
     */
    public function getStoreData();

    /**
     * Set storeData
     *
     * @param mixed $storeData
     * @return $this
     */
    public function setStoreData($storeData): StoreInterface;
}