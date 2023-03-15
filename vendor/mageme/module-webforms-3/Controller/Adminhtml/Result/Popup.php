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
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;

class Popup extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ResultFactory
     */
    protected $webformResultFactory;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Popup constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param ResultFactory $webformResultFactory
     * @param LayoutFactory $layoutFactory
     * @param AccessHelper $accessHelper
     * @param RawFactory $resultRawFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        ResultFactory             $webformResultFactory,
        LayoutFactory             $layoutFactory,
        AccessHelper              $accessHelper,
        RawFactory                $resultRawFactory,
        Registry                  $registry,
        Context                   $context)
    {
        parent::__construct($context);
        $this->registry             = $registry;
        $this->resultRawFactory     = $resultRawFactory;
        $this->accessHelper         = $accessHelper;
        $this->layoutFactory        = $layoutFactory;
        $this->webformResultFactory = $webformResultFactory;
        $this->resultRepository     = $resultRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = (int)$this->getRequest()->getParam(ResultInterface::ID);

        // 2. Initial checking or create model
        try {
            $result  = $id ? $this->resultRepository->getById($id) :
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

        $layout = $this->layoutFactory->create();

        // 5. Build edit form
        $resultRaw = $this->resultRawFactory->create();
        $html      = $layout->createBlock(\MageMe\WebForms\Block\Adminhtml\Result\Popup::class)
            ->toHtml();

        return $resultRaw->setContents($html);
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $id = (int)$this->getRequest()->getParam(ResultInterface::ID);
        if ($id) {
            $model = $this->resultRepository->getById($id);
            return $this->accessHelper->isAllowed($model->getFormId());
        }
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}
