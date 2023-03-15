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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class CustomersJson extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * CustomersJson constructor.
     * @param CollectionFactory $customerCollectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory,
        JsonFactory       $resultJsonFactory,
        Context           $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory         = $resultJsonFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute()
    {
        $q         = $this->getRequest()->getParam('term');
        $customers = [];
        if ($q) {
            $collection = $this->customerCollectionFactory->create()
                ->addNameToSelect()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->setPageSize(5);
            $collection->addAttributeToFilter(
                [
                    ['attribute' => 'email', 'like' => '%' . $q . '%'],
                    ['attribute' => 'name', 'like' => '%' . $q . '%'],
                ]
            );

            foreach ($collection as $customer) {
                $customers[] =
                    [
                        'label' => $customer->getFirstname() . ' ' . $customer->getLastname() . ' <' . $customer->getEmail() . '>',
                        'customerId' => $customer->getId()
                    ];
            }
        }
        return $this->resultJsonFactory->create()->setJsonData(json_encode($customers));
    }
}