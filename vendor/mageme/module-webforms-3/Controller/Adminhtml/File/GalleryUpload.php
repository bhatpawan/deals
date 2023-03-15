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

namespace MageMe\WebForms\Controller\Adminhtml\File;


use Exception;
use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;
use MageMe\WebForms\File\GalleryUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManager;

class GalleryUpload extends Action implements HttpPostActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var GalleryUploader
     */
    protected $uploader;

    /**
     * Upload constructor.
     * @param GalleryUploader $uploader
     * @param StoreManager $storeManager
     * @param Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        GalleryUploader $uploader,
        StoreManager    $storeManager,
        RawFactory      $resultRawFactory,
        Context         $context
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->storeManager     = $storeManager;
        $this->uploader         = $uploader;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $fieldId       = $this->getRequest()->getParam(TmpFileGalleryInterface::FIELD_ID);
            $tmpFile       = $this->uploader->saveFileToTmpDir($fieldId);
            $result        = [
                'file' => $tmpFile->getName(),
                'name' => $tmpFile->getName(),
                'size' => $tmpFile->getSize(),
                'type' => $tmpFile->getMimeType(),
                'hash' => $tmpFile->getHash(),
            ];
            $result['url'] = $this->storeManager->getStore()->getBaseUrl(
                    DirectoryList::MEDIA
                ) . $tmpFile->getPath();

        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
