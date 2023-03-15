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


interface QuickresponseInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'quickresponse_id';
    const QUICKRESPONSE_CATEGORY_ID = 'quickresponse_category_id';
    const TITLE = 'title';
    const MESSAGE = 'message';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
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
     * Get quickresponseCategoryId
     *
     * @return int|null
     */
    public function getQuickresponseCategoryId(): ?int;

    /**
     * Set quickresponseCategoryId
     *
     * @param int $quickresponseCategoryId
     * @return $this
     */
    public function setQuickresponseCategoryId(int $quickresponseCategoryId): QuickresponseInterface;


    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set title
     *
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): QuickresponseInterface;

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Set message
     *
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message): QuickresponseInterface;

    /**
     * Get createdTime
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set createdTime
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): QuickresponseInterface;

    /**
     * Get updateTime
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updateTime
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): QuickresponseInterface;

}