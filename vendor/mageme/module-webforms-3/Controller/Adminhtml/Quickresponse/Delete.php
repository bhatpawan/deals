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

namespace MageMe\WebForms\Controller\Adminhtml\Quickresponse;


use Exception;
use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    const INDEX_PATH = 'webforms/quickresponse/';

    /**
     * @var QuickresponseRepositoryInterface
     */
    private $quickresponseRepository;

    /**
     * Delete constructor.
     * @param QuickresponseRepositoryInterface $quickresponseRepository
     * @param Context $context
     */
    public function __construct(
        QuickresponseRepositoryInterface $quickresponseRepository,
        Context                          $context)
    {
        parent::__construct($context);
        $this->quickresponseRepository = $quickresponseRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id             = (int)$this->getRequest()->getParam(QuickresponseInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find a quick response to delete.'));
            return $resultRedirect->setPath(self::INDEX_PATH);
        }
        try {
            $this->quickresponseRepository->delete($this->quickresponseRepository->getById($id));
            $this->messageManager->addSuccessMessage(__('The quick response has been deleted.'));
            return $resultRedirect->setPath(self::INDEX_PATH);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', [QuickresponseInterface::ID => $id]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::quickresponse');
    }
}