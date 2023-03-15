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

namespace MageMe\WebForms\Controller\Adminhtml\Quickresponse;


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use MageMe\WebForms\Model\QuickresponseFactory;
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
     * @var QuickresponseFactory
     */
    protected $quickresponseFactory;

    /**
     * @var QuickresponseRepositoryInterface
     */
    protected $quickresponseRepository;

    /**
     * Edit constructor.
     * @param QuickresponseRepositoryInterface $quickresponseRepository
     * @param QuickresponseFactory $quickresponseFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        QuickresponseRepositoryInterface $quickresponseRepository,
        QuickresponseFactory             $quickresponseFactory,
        PageFactory                      $resultPageFactory,
        Registry                         $registry,
        Context                          $context)
    {
        parent::__construct($context);
        $this->registry                = $registry;
        $this->resultPageFactory       = $resultPageFactory;
        $this->quickresponseFactory    = $quickresponseFactory;
        $this->quickresponseRepository = $quickresponseRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = (int)$this->getRequest()->getParam(QuickresponseInterface::ID);

        // 2. Initial checking or create model
        try {
            $model = $id ? $this->quickresponseRepository->getById($id) :
                $this->quickresponseFactory->create();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This quick response no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        // 3. Set entered data if was error when we do save
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->registry->register('webforms_quickresponse', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::quickresponse')
            ->addBreadcrumb(__('WebForms'), __('WebForms'))
            ->addBreadcrumb(__('Manage Quick Responses'), __('Manage Quick Responses'));
        $resultPage->addBreadcrumb(
            $model->getId() ? __('Edit Quick Response') : __('New Quick Response'),
            $model->getId() ? __('Edit Quick Response') : __('New Quick Response')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Quick Responses'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Quick Response'));

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
