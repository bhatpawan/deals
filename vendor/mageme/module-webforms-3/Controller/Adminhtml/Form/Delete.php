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

namespace MageMe\WebForms\Controller\Adminhtml\Form;

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::delete_form';

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Delete constructor.
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        AccessHelper            $accessHelper,
        Context                 $context)
    {
        parent::__construct($context);
        $this->accessHelper   = $accessHelper;
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id             = (int)$this->getRequest()->getParam(FormInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {

            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find a form to delete.'));

            // go to grid
            return $resultRedirect->setPath('*/form/');
        }
        try {
            $this->formRepository->delete($this->formRepository->getById($id));

            // display success message
            $this->messageManager->addSuccessMessage(__('The form has been deleted.'));
            return $resultRedirect->setPath('*/form/');
        } catch (Exception $e) {

            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());

            // go back to edit form
            return $resultRedirect->setPath('*/*/edit', [FormInterface::ID => $id]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($id && !$this->accessHelper->isAllowed($id)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }
}