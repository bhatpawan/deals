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

namespace MageMe\WebForms\Controller\Adminhtml\Field;

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;

class Delete extends AbstractFieldAction
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id             = (int)$this->getRequest()->getParam(FieldInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {

            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find a field to delete.'));

            // go to grid
            return $resultRedirect->setPath('*/form/');
        }
        try {
            $field = $this->repository->getById($id);
            $this->repository->delete($field);

            // display success message
            $this->messageManager->addSuccessMessage(__('The field has been deleted.'));
            return $resultRedirect->setPath('*/form/',
                [FormInterface::ID => $field->getFormId(), 'active_tab' => 'fields_section']);
        } catch (Exception $e) {

            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());

            // go back to edit form
            return $resultRedirect->setPath('*/*/edit', [FieldInterface::ID => $id]);
        }
    }
}