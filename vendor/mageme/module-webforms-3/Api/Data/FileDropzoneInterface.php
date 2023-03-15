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


interface FileDropzoneInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'file_dropzone_id';
    const RESULT_ID = 'result_id';
    const FIELD_ID = 'field_id';
    const NAME = 'name';
    const SIZE = 'size';
    const MIME_TYPE = 'mime_type';
    const PATH = 'path';
    const LINK_HASH = 'link_hash';
    const CREATED_AT = 'created_at';
    /**#@-*/

#region DB getters and setters
    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set ID
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get resultId
     *
     * @return int|null
     */
    public function getResultId(): ?int;

    /**
     * Set resultId
     *
     * @param int $resultId
     * @return $this
     */
    public function setResultId(int $resultId): FileDropzoneInterface;

    /**
     * Get fieldId
     *
     * @return int|null
     */
    public function getFieldId(): ?int;

    /**
     * Set fieldId
     *
     * @param int $fieldId
     * @return $this
     */
    public function setFieldId(int $fieldId): FileDropzoneInterface;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): FileDropzoneInterface;

    /**
     * Get size
     *
     * @return int|null
     */
    public function getSize(): ?int;

    /**
     * Set size
     *
     * @param int|null $size
     * @return $this
     */
    public function setSize(?int $size): FileDropzoneInterface;

    /**
     * Get mimeType
     *
     * @return string|null
     */
    public function getMimeType(): ?string;

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType(string $mimeType): FileDropzoneInterface;

    /**
     * Get path
     *
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * Set path
     *
     * @param string $path
     * @return $this
     */
    public function setPath(string $path): FileDropzoneInterface;

    /**
     * Get linkHash
     *
     * @return string|null
     */
    public function getLinkHash(): ?string;

    /**
     * Set linkHash
     *
     * @param string $linkHash
     * @return $this
     */
    public function setLinkHash(string $linkHash): FileDropzoneInterface;

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
    public function setCreatedAt(?string $createdAt): FileDropzoneInterface;
#endregion

    /**
     * Get file size annotation
     *
     * @return string
     */
    public function getSizeText(): string;

    /**
     * Get full path to file
     *
     * @return string
     */
    public function getFullPath(): string;
}