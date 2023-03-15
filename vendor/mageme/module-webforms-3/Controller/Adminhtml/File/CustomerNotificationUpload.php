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
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\File\CustomerNotificationUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManager;

class CustomerNotificationUpload extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var CustomerNotificationUploader
     */
    private $uploader;

    /**
     * CustomerNotificationUpload constructor.
     * @param CustomerNotificationUploader $uploader
     * @param StoreManager $storeManager
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        CustomerNotificationUploader $uploader,
        StoreManager                 $storeManager,
        JsonFactory                  $resultJsonFactory,
        Context                      $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager      = $storeManager;
        $this->uploader          = $uploader;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $fieldId       = $this->getRequest()->getParam('param_name');
            $formId        = (int)$this->getRequest()->getParam(FormInterface::ID);
            $tmpFile       = $this->uploader->saveFileToTmpDir($fieldId, $formId);
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
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}