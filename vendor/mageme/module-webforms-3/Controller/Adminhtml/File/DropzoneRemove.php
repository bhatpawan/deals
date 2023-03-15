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

use MageMe\WebForms\Model\Repository\TmpFileDropzoneRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class DropzoneRemove extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var TmpFileDropzoneRepository
     */
    private $tmpFileDropzoneRepository;

    /**
     * @param TmpFileDropzoneRepository $tmpFileDropzoneRepository
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        TmpFileDropzoneRepository $tmpFileDropzoneRepository,
        JsonFactory               $resultJsonFactory,
        Context                   $context
    ) {
        parent::__construct($context);
        $this->resultJsonFactory         = $resultJsonFactory;
        $this->tmpFileDropzoneRepository = $tmpFileDropzoneRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'error' => ''
        ];
        $hash   = $this->getRequest()->getParam('hash');
        try {
            $file = $this->tmpFileDropzoneRepository->getByHash($hash);
            $this->tmpFileDropzoneRepository->delete($file);
            $result['success'] = true;
        } catch (NoSuchEntityException|CouldNotDeleteException $e) {
            $result['error'] = $e->getMessage();
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