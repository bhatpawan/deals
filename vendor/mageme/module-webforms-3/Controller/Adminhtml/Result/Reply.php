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


use IntlDateFormatter;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;

class Reply extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::reply';

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
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var ResultFactory
     */
    protected $webformResultFactory;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Reply constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param ResultFactory $webformResultFactory
     * @param TimezoneInterface $localeDate
     * @param AccessHelper $accessHelper
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        ResultFactory             $webformResultFactory,
        TimezoneInterface         $localeDate,
        AccessHelper              $accessHelper,
        PageFactory               $resultPageFactory,
        Registry                  $registry,
        Context                   $context)
    {
        parent::__construct($context);
        $this->registry             = $registry;
        $this->resultPageFactory    = $resultPageFactory;
        $this->accessHelper         = $accessHelper;
        $this->localeDate           = $localeDate;
        $this->webformResultFactory = $webformResultFactory;
        $this->resultRepository     = $resultRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = (int)$this->getRequest()->getParam(ResultInterface::ID);

        // 2. Initial checking or create model
        try {
            $result = $id ? $this->resultRepository->getById($id) :
                $this->webformResultFactory->create();
            $formId = $result->getFormId() ? $result->getFormId() :
                (int)$this->getRequest()->getParam(FormInterface::ID);
            $result->setIsRead(1);
            $this->resultRepository->save($result);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->messageManager->addErrorMessage(__('This result no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if (!$formId) {
            $this->messageManager->addErrorMessage(__('Form identifier is not specified.'));
            return $this->resultRedirectFactory->create()->setPath('*/form/');
        }
        $result->setFormId($formId);
        try {
            $modelForm = $result->getForm();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This form no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/form/');
        }

        // 3. Set entered data if was error when we do save
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $result->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->registry->register('webforms_result', $result);
        $this->registry->register('webforms_form', $modelForm);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::manage_forms')
            ->addBreadcrumb(__('WebForms'), __('WebForms'))
            ->addBreadcrumb(__('Reply'), __('Reply'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Result') : __('New Result'),
            $id ? __('Edit Result') : __('New Result')
        );
        $resultPage->getConfig()->getTitle()
            ->prepend($result->getId() ? __("Result # %1 | %2", $result->getId(),
                $this->localeDate->formatDate($result->getCreatedAt(), IntlDateFormatter::MEDIUM,
                    true)) : __('New Result'));

        return $resultPage;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(ResultInterface::ID);
        if ($id) {
            $model  = $this->resultRepository->getById($id);
            $formId = $model->getFormId();
            if ($formId && !$this->accessHelper->isAllowed($formId)) {
                $isAllowed = false;
            }
        }
        return $isAllowed;
    }
}
