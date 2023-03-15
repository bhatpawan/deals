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

namespace MageMe\WebForms\Block\Adminhtml\Result\Autocomplete;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\CustomerFactory;

/**
 *
 */
class Customer extends Template
{
    /**
     * @var string
     */
    protected $_template = 'result/element/customer.phtml';

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context         $context,
        CustomerFactory $customerFactory,
        array           $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param int $customerId
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(int $customerId = 0): \Magento\Customer\Model\Customer
    {
        $customer = $this->_customerFactory->create();
        if ($customerId) {
            $customer->load($customerId);
        }
        return $customer;
    }

    /**
     * @return string
     */
    public function getAutocompleteUrl(): string
    {
        return $this->getUrl('webforms/result/customersJson');
    }
}
