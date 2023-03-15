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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\FormFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Edit constructor.
     * @param FormRepositoryInterface $formRepository
     * @param FormFactory $formFactory
     * @param AccessHelper $accessHelper
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        FormFactory             $formFactory,
        AccessHelper            $accessHelper,
        PageFactory             $resultPageFactory,
        Registry                $registry,
        Context                 $context)
    {
        parent::__construct($context);
        $this->registry          = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->accessHelper      = $accessHelper;
        $this->formFactory       = $formFactory;
        $this->formRepository    = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id      = $this->getRequest()->getParam(FormInterface::ID);
        $storeId = $this->getRequest()->getParam('store');

        // 2. Initial checking or create model
        try {
            $model = $id ? $this->formRepository->getById($id, $storeId) :
                $this->formFactory->create()->setStoreId($storeId);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This form no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        // 3. Set entered data if was error when we do save
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->registry->register('webforms_form', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::manage_forms')
            ->addBreadcrumb(__('WebForms'), __('WebForms'))
            ->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Form') : __('New Form'),
            $id ? __('Edit Form') : __('New Form')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Forms'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Form'));

        // 6. Remove store view switcher if the entity is new
        if (!$model->getId()) {
            $resultPage->getLayout()->getBlock('store_switcher')->setTemplate(false);
        }

        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        $id = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($id) {
            return $this->accessHelper->isAllowed($id);
        }
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}