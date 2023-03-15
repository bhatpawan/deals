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

namespace MageMe\WebForms\Controller\Adminhtml\Field;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\FieldFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends AbstractFieldAction
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
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @param FieldFactory $fieldFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param FieldRepositoryInterface $fieldRepository
     * @param AccessHelper $accessHelper
     * @param Action\Context $context
     */
    public function __construct(
        FieldFactory             $fieldFactory,
        PageFactory              $resultPageFactory,
        Registry                 $registry,
        FieldRepositoryInterface $fieldRepository,
        AccessHelper             $accessHelper,
        Action\Context           $context)
    {
        parent::__construct($fieldRepository, $accessHelper, $context);
        $this->registry          = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->fieldFactory      = $fieldFactory;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute()
    {
        // 1. Get ID
        $id      = (int)$this->getRequest()->getParam(FieldInterface::ID);
        $storeId = $this->getRequest()->getParam('store');

        // 2. Initial checking or create model
        try {
            $model = $id ? $this->repository->getById($id,
                $storeId) : $this->fieldFactory->create()->setStoreId($storeId);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This field no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if (!$model->getFormId()) {
            $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
            if (!$formId) {
                $this->messageManager->addErrorMessage(__('Form identifier is not specified.'));
                return $this->resultRedirectFactory->create()->setPath('*/form/');
            }
            $model->setFormId($formId);
        }
        try {
            $modelForm = $model->getForm();
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This form no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/form/');
        }

        // 3. Set entered data if was error when we do save
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register models to use later in blocks
        $this->registry->register('webforms_field', $model);
        $this->registry->register('webforms_form', $modelForm);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::manage_forms')
            ->addBreadcrumb(__('WebForms'), __('WebForms'))
            ->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        $resultPage->addBreadcrumb(
            $model->getId() ? __('Edit Field') : __('New Field'),
            $model->getId() ? __('Edit Field') : __('New Field')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Fields'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Field'));

        // 6. Remove store view switcher if the entity is new
        if (!$model->getId()) {
            $resultPage->getLayout()->getBlock('store_switcher')->setTemplate(false);
        }
        return $resultPage;
    }
}