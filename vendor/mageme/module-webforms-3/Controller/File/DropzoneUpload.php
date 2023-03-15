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

namespace MageMe\WebForms\Controller\File;

use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\File\DropzoneUploader;
use MageMe\WebForms\Model\Field\Type\File;
use MageMe\WebForms\Model\Field\Type\Image;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\HttpFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class DropzoneUpload extends AbstractAction
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var HttpFactory
     */
    protected $httpFactory;

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
     *
     * @param Context $context
     * @param DropzoneUploader $dropzoneUploader
     * @param FieldRepositoryInterface $fieldRepository
     * @param HttpFactory $httpFactory
     * @param StoreManagerInterface $storeManager
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                  $context,
        DropzoneUploader         $dropzoneUploader,
        FieldRepositoryInterface $fieldRepository,
        HttpFactory              $httpFactory,
        StoreManagerInterface    $storeManager,
        PageFactory              $pageFactory
    ) {
        parent::__construct($context, $pageFactory);
        $this->_storeManager   = $storeManager;
        $this->httpFactory     = $httpFactory;
        $this->fieldRepository = $fieldRepository;
        $this->uploader        = $dropzoneUploader;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result = [
            'hash' => '',
            'error' => ''
        ];

        $fileId  = (string)$this->request->getParam('file_id');
        $fieldId = (int)str_replace('file_', '', $fileId);

        /** @var File|Image $field */
        $field           = $this->fieldRepository->getById(
            $fieldId,
            $this->_storeManager->getStore()->getId()
        );
        $file            = $this->request->getFiles($fileId);
        $result['error'] = $field->validateFile($file);
        if (!$result['error']) {
            $file           = $this->uploader->saveFileToTmpDir($fileId);
            $result['hash'] = $file->getHash();
        }
        $json       = json_encode($result);
        $resultHttp = $this->httpFactory->create();
        $resultHttp->setNoCacheHeaders();
        $resultHttp->setHeader('Content-Type', 'text/plain', true);
        $resultHttp->setHeader('X-Robots-Tag', 'noindex', true);
        return $resultHttp->setContent($json);
    }
}
