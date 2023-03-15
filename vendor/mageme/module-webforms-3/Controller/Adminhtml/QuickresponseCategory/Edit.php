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

namespace MageMe\WebForms\Controller\Adminhtml\QuickresponseCategory;


use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use MageMe\WebForms\Model\QuickresponseCategoryFactory;
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
     * @var QuickresponseCategoryFactory
     */
    protected $quickresponseCategoryFactory;

    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    protected $quickresponseCategoryRepository;

    /**
     * Edit constructor.
     * @param QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
     * @param QuickresponseCategoryFactory $quickresponseCategoryFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository,
        QuickresponseCategoryFactory             $quickresponseCategoryFactory,
        PageFactory                              $resultPageFactory,
        Registry                                 $registry,
        Context                                  $context)
    {
        parent::__construct($context);
        $this->registry                        = $registry;
        $this->resultPageFactory               = $resultPageFactory;
        $this->quickresponseCategoryFactory    = $quickresponseCategoryFactory;
        $this->quickresponseCategoryRepository = $quickresponseCategoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = (int)$this->getRequest()->getParam(QuickresponseCategoryInterface::ID);

        // 2. Initial checking or create model
        try {
            $model = $id ? $this->quickresponseCategoryRepository->getById($id) :
                $this->quickresponseCategoryFactory->create();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This quick response category no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        // 3. Set entered data if was error when we do save
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->registry->register('webforms_quickresponse_category', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::quickresponse')
            ->addBreadcrumb(__('WebForms'), __('WebForms'))
            ->addBreadcrumb(__('Manage Quick Responses'), __('Manage Quick Responses'));
        $resultPage->addBreadcrumb(
            $model->getId() ? __('Edit Category') : __('New Category'),
            $model->getId() ? __('Edit Category') : __('New Category')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Category'));

        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::quickresponse');
    }
}