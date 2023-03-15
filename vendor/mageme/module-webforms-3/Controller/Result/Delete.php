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

namespace MageMe\WebForms\Controller\Result;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Config\Options\Result\Permission;
use MageMe\WebForms\Controller\ResultAction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 * @package MageMe\WebForms\Controller\Result
 */
class Delete extends ResultAction
{
    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->_init();
        $webform = $this->result->getForm();
        if (!in_array(Permission::DELETE, $webform->getCustomerResultPermissionsByResult($this->result))) {
            return $this->redirect('customer/account');
        }
        $this->resultRepository->delete($this->result);
        $this->messageManager->addSuccessMessage(__('The record has been deleted.'));
        return $this->redirect('webforms/customer/account', [ResultInterface::FORM_ID => $webform->getId()]);
    }
}
