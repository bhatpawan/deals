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

namespace MageMe\WebForms\Controller\Adminhtml\Logic;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\LogicFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::save_form';

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
     * @var LogicFactory
     */
    protected $logicFactory;

    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Edit constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param LogicRepositoryInterface $logicRepository
     * @param LogicFactory $logicFactory
     * @param AccessHelper $accessHelper
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        LogicRepositoryInterface $logicRepository,
        LogicFactory             $logicFactory,
        AccessHelper             $accessHelper,
        PageFactory              $resultPageFactory,
        Registry                 $registry,
        Context                  $context)
    {
        parent::__construct($context);
        $this->registry          = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->accessHelper      = $accessHelper;
        $this->logicFactory      = $logicFactory;
        $this->logicRepository   = $logicRepository;
        $this->fieldRepository   = $fieldRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id      = (int)$this->getRequest()->getParam(LogicInterface::ID);
        $storeId = $this->getRequest()->getParam('store');

        // 2. Initial checking or create model
        try {
            $model   = $id ? $this->logicRepository->getById($id, $storeId) :
                $this->logicFactory->create()->setStoreId($storeId);
            $fieldId = $model->getFieldId() ? $model->getFieldId() :
                (int)$this->getRequest()->getParam(LogicInterface::FIELD_ID);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This logic no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if (!$fieldId) {
            $this->messageManager->addErrorMessage(__('Field identifier is not specified.'));
            return $this->resultRedirectFactory->create()->setPath('*/form/');
        }
        try {
            $modelField = $this->fieldRepository->getById($fieldId, $storeId);
            $model->setFieldId($fieldId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This field no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/field/edit', [FieldInterface::ID => $fieldId]);
        }

        // 3. Set entered data if was error when we do save
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register models to use later in blocks
        $this->registry->register('webforms_field', $modelField);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::manage_forms')
            ->addBreadcrumb(__('WebForms'), __('WebForms'))
            ->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        $resultPage->addBreadcrumb(
            $model->getId() ? __('Edit Logic') : __('New Logic'),
            $model->getId() ? __('Edit Logic') : __('New Logic')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Logic'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Logic') : __('New Logic'));

        return $resultPage;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(LogicInterface::ID);
        $fieldId   = (int)$this->getRequest()->getParam(LogicInterface::FIELD_ID);
        if ($id) {
            $logic   = $this->logicRepository->getById($id);
            $fieldId = $logic->getFieldId() ? $logic->getFieldId() : $fieldId;
        }
        if ($fieldId) {
            $field  = $this->fieldRepository->getById($fieldId);
            $formId = $field->getFormId();
            if ($formId && !$this->accessHelper->isAllowed($formId)) {
                $isAllowed = false;
            }
        }
        return $isAllowed;
    }
}
