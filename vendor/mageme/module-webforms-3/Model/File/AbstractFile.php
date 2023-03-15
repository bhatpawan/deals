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


use MageMe\WebForms\Model\TmpFile\AbstractTmpFile;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Image\Factory;
use Magento\Framework\UrlInterface;

abstract class AbstractFile extends AbstractTmpFile
{
    const CACHE_TAG = 'webforms_file';
    const THUMBNAIL_DIR = 'webforms/thumbs';

    /**
     * @var Factory
     */
    protected $imageFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AbstractFile constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct(
            $context->getStoreManager(),
            $context->getContext(),
            $context->getRegistry(),
            $context->getResource(),
            $context->getResourceCollection(),
            $context->getData());
        $this->imageFactory = $context->getImageFactory();
        $this->scopeConfig  = $context->getScopeConfig();
    }

    /**
     * @return string
     */
    public function getSizeText(): string
    {
        $size  = $this->getSize();
        $sizes = [" bytes", " kb", " mb", " gb", " tb", " pb", " eb", " zb", " yb"];
        if ($size == 0) {
            return ('n/a');
        }
        return (round($size / pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[intval($i)]);
    }

    /**
     * @param int|bool $width
     * @param int|bool $height
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getThumbnail($width = false, $height = false)
    {
        $imageUrl = $this->getFullPath();

        $file_info = @getimagesize($imageUrl);

        if (!$file_info) {
            return false;
        }

        if (strstr((string)$file_info["mime"], "bmp")) {
            return false;
        }
        if (!$height && !empty($file_info[0]) && !empty($file_info[1])) {
            $height = round($file_info[1] * ($width / $file_info[0]));
        }

        $thumbnail_filename = substr((string)$this->getData('link_hash'), -10) . '_' . $width . 'x' . $height;
        if (file_exists($imageUrl)) {

            $imageResized = $this->getThumbnailDir() . '/' . $thumbnail_filename;
            if (!file_exists($imageResized) || $this->scopeConfig->getValue('webforms/images/cache') == 0) {

                if (!file_exists($this->getThumbnailDir())) {
                    mkdir($this->getThumbnailDir());
                }

                $this->setMemoryForImage();
                $imageObj = @$this->imageFactory->create($imageUrl);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepTransparency(true);
                $imageObj->resize($width, $height);
                $imageObj->save($imageResized);
                unset($imageObj);
            }
        } else {
            return false;
        }

        $url = $this->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::THUMBNAIL_DIR;
        $url .= '/' . $thumbnail_filename;
        return $url;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getThumbnailDir(): string
    {
        return $this->getStore()->getBaseMediaDir() . '/' . static::THUMBNAIL_DIR;
    }

    /**
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function setMemoryForImage()
    {
        $filename     = $this->getFullPath();
        $imageInfo    = getimagesize($filename);
        $MB           = 1048576;  // number of bytes in 1M
        $K64          = 65536;    // number of bytes in 64K
        $TWEAK_FACTOR = 1.5;  // Or whatever works for you
        if (empty($imageInfo['bits']) || empty($imageInfo['channels'])) {
            return false;
        }
        $memoryNeeded = round(($imageInfo[0] * $imageInfo[1]
                * $imageInfo['bits']
                * $imageInfo['channels'] / 8
                + $K64
            ) * $TWEAK_FACTOR
        );
        $defaultLimit = ini_get('memory_limit');
        $memoryLimit  = $defaultLimit;
        if (preg_match('/^(\d+)(.)$/', (string)$defaultLimit, $matches)) {
            if ($matches[2] == 'G') {
                return false;
            }
            if ($matches[2] == 'M') {
                $memoryLimit = intval($matches[1]) * 1024 * 1024; // nnnM -> nnn MB
            } else {
                if ($matches[2] == 'K') {
                    $memoryLimit = intval($matches[1]) * 1024; // nnnK -> nnn KB
                }
            }
        }
        if (function_exists('memory_get_usage') &&
            memory_get_usage() + $memoryNeeded > $memoryLimit
        ) {
            $newLimit = $memoryLimit + ceil((memory_get_usage()
                        + $memoryNeeded
                        - $memoryLimit
                    ) / $MB
                );
            ini_set('memory_limit', $newLimit . 'M');
            return $defaultLimit;
        } else {
            return false;
        }
    }
}
