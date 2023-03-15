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


use MageMe\WebForms\Model\File\AbstractFileGallery;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class FileGallery extends AbstractFileGallery
{
    /**
     * Get image URL
     *
     * @param int|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl(?int $storeId): string
    {
        return $this->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getPath();
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\FileGallery::class);
    }
}
