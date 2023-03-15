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
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Index constructor.
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        AccessHelper            $accessHelper,
        PageFactory             $resultPageFactory,
        Registry                $registry,
        Context                 $context)
    {
        parent::__construct($context);
        $this->registry          = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->accessHelper      = $accessHelper;
        $this->formRepository    = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
        $store  = $this->getRequest()->getParam('store');
        if (!$formId) {
            $this->messageManager->addErrorMessage(__('Form identifier is not specified.'));
            return $this->resultRedirectFactory->create()->setPath('*/form/');
        }
        try {
            $form = $this->formRepository->getById($formId, $store);
            $this->registry->register('webforms_form', $form);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This form no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/form/');
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::manage_forms');
        $resultPage->addBreadcrumb(__('WebForms'), __('WebForms'));
        $resultPage->addBreadcrumb(__('Manage Results'), __('Manage Results'));
        $resultPage->getConfig()->getTitle()->prepend($form->getName());

        return $resultPage;
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