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

namespace MageMe\WebForms\Controller\Adminhtml\Fieldset;

use Exception;
use MageMe\WebForms\Api\Data\FieldsetInterface;

class Delete extends AbstractFieldsetAction
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id             = (int)$this->getRequest()->getParam(FieldsetInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {

            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find a fieldset to delete.'));

            // go to grid
            return $resultRedirect->setPath('*/form/');
        }
        try {
            $fieldset = $this->repository->getById($id);
            $this->repository->delete($fieldset);

            // display success message
            $this->messageManager->addSuccessMessage(__('The fieldset has been deleted.'));
            return $resultRedirect->setPath('*/form/',
                [FieldsetInterface::FORM_ID => $fieldset->getFormId(), 'active_tab' => 'fieldsets_section']);
        } catch (Exception $e) {

            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());

            // go back to edit fieldset
            return $resultRedirect->setPath('*/*/edit', [FieldsetInterface::ID => $id]);
        }
    }
}