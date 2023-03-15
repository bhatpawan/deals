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

namespace MageMe\WebForms\Controller\Adminhtml\Result;


use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::edit_result';

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Delete constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        AccessHelper              $accessHelper,
        Context                   $context
    )
    {
        parent::__construct($context);
        $this->accessHelper     = $accessHelper;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id             = (int)$this->getRequest()->getParam(ResultInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {

            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find a result to delete.'));

            // go to grid
            return $resultRedirect->setPath('*/form/');
        }
        try {
            $model = $this->resultRepository->getById($id);
            $this->resultRepository->delete($model);

            // display success message
            $this->messageManager->addSuccessMessage(__('The result has been deleted.'));
            return $resultRedirect->setPath('*/*/', [FormInterface::ID => $model->getFormId()]);
        } catch (Exception $e) {

            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());

            // go back to edit result
            return $resultRedirect->setPath('*/*/edit', [ResultInterface::ID => $id]);
        }
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(ResultInterface::ID);
        if ($id) {
            $model  = $this->resultRepository->getById($id);
            $formId = $model->getFormId();
            if ($formId && !$this->accessHelper->isAllowed($formId)) {
                $isAllowed = false;
            }
        }
        return $isAllowed;
    }
}