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

namespace MageMe\WebForms\Controller\Adminhtml\QuickresponseCategory;


use Exception;
use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    const INDEX_PATH = 'webforms/quickresponsecategory/';

    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    private $quickresponseCategoryRepository;

    /**
     * Delete constructor.
     * @param QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
     * @param Context $context
     */
    public function __construct(
        QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository,
        Context                                  $context)
    {
        parent::__construct($context);
        $this->quickresponseCategoryRepository = $quickresponseCategoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id             = (int)$this->getRequest()->getParam(QuickresponseCategoryInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find a quick response category to delete.'));
            return $resultRedirect->setPath(self::INDEX_PATH);
        }
        try {
            $this->quickresponseCategoryRepository->delete($this->quickresponseCategoryRepository->getById($id));
            $this->messageManager->addSuccessMessage(__('The quick response category has been deleted.'));
            return $resultRedirect->setPath(self::INDEX_PATH);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', [QuickresponseCategoryInterface::ID => $id]);
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