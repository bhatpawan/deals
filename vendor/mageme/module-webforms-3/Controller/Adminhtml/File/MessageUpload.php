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
use MageMe\WebForms\File\MessageUploader;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class MessageUpload extends Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var MessageUploader
     */
    protected $uploader;

    /**
     * MessageUpload constructor.
     * @param MessageUploader $uploader
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param Action\Context $context
     */
    public function __construct(
        MessageUploader       $uploader,
        JsonFactory           $resultJsonFactory,
        StoreManagerInterface $storeManager,
        Action\Context        $context
    )
    {
        parent::__construct($context);
        $this->storeManager      = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->uploader          = $uploader;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result          = [];
        $result['hash']  = '';
        $result['error'] = [];
        $fileId          = $this->getRequest()->getParam('file_id');
        try {
            $file           = $this->uploader->saveFileToTmpDir($fileId);
            $result['hash'] = $file->getHash();
        } catch (Exception $exception) {
            $result['error'][] = $exception->getMessage();
        }
        $json       = json_encode($result);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData($json);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}
