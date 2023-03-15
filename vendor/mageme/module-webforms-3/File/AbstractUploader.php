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

namespace MageMe\WebForms\File;


use finfo;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManager;

abstract class AbstractUploader
{
    const UPLOAD_DIR = 'webforms/upload';

    const TMP_DIR = 'webforms/tmp';

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * AbstractUploader constructor.
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManager $storeManager
     * @param Random $random
     */
    public function __construct(
        Filesystem      $fileSystem,
        UploaderFactory $uploaderFactory,
        StoreManager    $storeManager,
        Random          $random
    )
    {
        $this->random          = $random;
        $this->storeManager    = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileSystem      = $fileSystem;
    }

    /**
     * @param $path
     * @return string
     */
    public function getMimeType($path): string
    {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type  = $finfo->file($path);
            return $type;
        }
        return '';
    }

    /**
     * Get file path
     *
     * @param string $file
     * @return string
     */
    public function getFilePath(string $file): string
    {
        return $this->getPath() . '/' . $this->prepareFile($file);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return static::UPLOAD_DIR;
    }

    /**
     * Process file name
     *
     * @param string $file
     * @return string
     */
    public function prepareFile(string $file): string
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Get file path
     *
     * @param string $file
     * @return string
     */
    public function getTmpFilePath(string $file): string
    {
        return $this->getTmpPath() . '/' . $this->prepareFile($file);
    }

    /**
     * @return string
     */
    public function getTmpPath(): string
    {
        return static::TMP_DIR;
    }

    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . '/' . $this->getPath();
    }

    /**
     * @return string
     */
    public function getTmpDir(): string
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . '/' . $this->getTmpPath();
    }
}
