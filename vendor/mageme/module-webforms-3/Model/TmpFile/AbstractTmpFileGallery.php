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

namespace MageMe\WebForms\Model\TmpFile;


use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;

abstract class AbstractTmpFileGallery extends AbstractTmpFile implements TmpFileGalleryInterface
{

    const CACHE_TAG = parent::CACHE_TAG . '_gallery';

#region DB getters and setters

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

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
    public function getFieldId(): ?int
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setFieldId(?int $fieldId): TmpFileGalleryInterface
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): TmpFileGalleryInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->getData(self::SIZE);
    }

    /**
     * @inheritDoc
     */
    public function setSize(?int $size): TmpFileGalleryInterface
    {
        return $this->setData(self::SIZE, $size);
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(): ?string
    {
        return $this->getData(self::MIME_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setMimeType(string $mimeType): TmpFileGalleryInterface
    {
        return $this->setData(self::MIME_TYPE, $mimeType);
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ?string
    {
        return $this->getData(self::PATH);
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): TmpFileGalleryInterface
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @inheritDoc
     */
    public function getHash(): ?string
    {
        return $this->getData(self::HASH);
    }

    /**
     * @inheritDoc
     */
    public function setHash(string $hash): TmpFileGalleryInterface
    {
        return $this->setData(self::HASH, $hash);
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
    public function setCreatedAt(?string $createdAt): TmpFileGalleryInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

#endregion
}
