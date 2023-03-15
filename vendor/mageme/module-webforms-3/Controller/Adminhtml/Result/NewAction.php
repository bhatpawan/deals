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


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * NewAction constructor.
     * @param AccessHelper $accessHelper
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     */
    public function __construct(
        AccessHelper   $accessHelper,
        ForwardFactory $resultForwardFactory,
        Context        $context)
    {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->accessHelper         = $accessHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($formId) {
            return $this->accessHelper->isAllowed($formId);
        }
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}