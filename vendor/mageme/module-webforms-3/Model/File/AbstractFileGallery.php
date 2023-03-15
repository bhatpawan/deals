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

namespace MageMe\WebForms\Model\File;


use MageMe\WebForms\Api\Data\FileGalleryInterface;
use MageMe\WebForms\Model\Repository\FieldRepository;

abstract class AbstractFileGallery extends AbstractFile implements FileGalleryInterface
{
    const CACHE_TAG = parent::CACHE_TAG . '_gallery';

    /**
     * @var FieldRepository
     */
    protected $fieldRepository;

    /**
     * AbstractFileGallery constructor.
     * @param FieldRepository $fieldRepository
     * @param Context $context
     */
    public function __construct(
        FieldRepository $fieldRepository,
        Context         $context)
    {
        parent::__construct($context);
        $this->fieldRepository = $fieldRepository;
    }

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
    public function setFieldId(int $fieldId): FileGalleryInterface
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
    public function setName(string $name): FileGalleryInterface
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
    public function setSize(?int $size): FileGalleryInterface
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
    public function setMimeType(string $mimeType): FileGalleryInterface
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
    public function setPath(string $path): FileGalleryInterface
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @inheritDoc
     */
    public function getLinkHash(): ?string
    {
        return $this->getData(self::LINK_HASH);
    }

    /**
     * @inheritDoc
     */
    public function setLinkHash(string $linkHash): FileGalleryInterface
    {
        return $this->setData(self::LINK_HASH, $linkHash);
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
    public function setCreatedAt(?string $createdAt): FileGalleryInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

#endregion
}
