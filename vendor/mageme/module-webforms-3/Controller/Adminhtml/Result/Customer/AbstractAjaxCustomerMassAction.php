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

namespace MageMe\WebForms\Controller\Adminhtml\Result\Customer;


use MageMe\WebForms\Controller\Adminhtml\Result\AbstractAjaxResultMassAction;
use Magento\Framework\Data\Collection\AbstractDb;

abstract class AbstractAjaxCustomerMassAction extends AbstractAjaxResultMassAction
{
    /**
     * @inheritDoc
     */
    protected function getCollection(): AbstractDb
    {
        $collection   = parent::getCollection();
        $customerData = $this->_session->getData('customer_data');
        if ($customerData && $customerData['customer_id']) {
            $collection->addFieldToFilter('customer_id', $customerData['customer_id']);
        }
        return $collection;
    }

}