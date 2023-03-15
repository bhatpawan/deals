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


use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\File\DropzoneUploader;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class DropzoneUpload extends Action
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
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var DropzoneUploader
     */
    protected $uploader;

    /**
     * DropzoneUpload constructor.
     * @param DropzoneUploader $uploader
     * @param FieldRepositoryInterface $fieldRepository
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param Action\Context $context
     */
    public function __construct(
        DropzoneUploader         $uploader,
        FieldRepositoryInterface $fieldRepository,
        JsonFactory              $resultJsonFactory,
        StoreManagerInterface    $storeManager,
        Action\Context           $context
    )
    {
        parent::__construct($context);
        $this->storeManager      = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fieldRepository   = $fieldRepository;
        $this->uploader          = $uploader;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result          = [];
        $result['hash']  = '';
        $result['error'] = [];

        $fileId  = (string)$this->getRequest()->getParam('file_id');
        $fieldId = (int)str_replace('file_', '', $fileId);
        if ($fieldId) {
            $field           = $this->fieldRepository->getById(
                $fieldId,
                $this->storeManager->getStore()->getId()
            );
            $file            = $this->_request->getFiles($fileId);
            $result['error'] = $field->validateFile($file);
            if (!$result['error']) {
                $file           = $this->uploader->saveFileToTmpDir($fileId);
                $result['hash'] = $file->getHash();
            }
        } else {
            $result['error'][] = __('Field id is not specified.');
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
