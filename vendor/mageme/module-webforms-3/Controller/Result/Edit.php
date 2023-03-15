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
 * Class Edit
 * @package MageMe\WebForms\Controller\Result
 */
class Edit extends ResultAction
{
    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->_init();
        $form = $this->result->getForm();
        if (!in_array(Permission::EDIT, $form->getCustomerResultPermissionsByResult($this->result))) {
            return $this->redirect('webforms/customer/account', [ResultInterface::FORM_ID => $form->getId()]);
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getLayout()->getBlock('webforms_customer_account_form_edit')
            ->setData(ResultInterface::FORM_ID, $form->getId())
            ->setResult($this->result);
        $resultPage->getConfig()->getTitle()->set($this->result->getSubject());

        return $resultPage;
    }
}
