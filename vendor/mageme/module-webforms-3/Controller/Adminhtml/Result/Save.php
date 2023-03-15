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
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Helper\Result\PostHelper;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend_Validate_Exception;

class Save extends Action
{
    const ADMIN_SAVE = 'admin_save';

    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::edit_result';

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var ResultFactory
     */
    protected $webformResultFactory;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var PostHelper
     */
    protected $resultPostHelper;

    /**
     * Save constructor.
     * @param PostHelper $resultPostHelper
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param ResultFactory $webformResultFactory
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        PostHelper                $resultPostHelper,
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        ResultFactory             $webformResultFactory,
        AccessHelper              $accessHelper,
        Context                   $context)
    {
        parent::__construct($context);
        $this->accessHelper         = $accessHelper;
        $this->webformResultFactory = $webformResultFactory;
        $this->resultRepository     = $resultRepository;
        $this->formRepository       = $formRepository;
        $this->resultPostHelper     = $resultPostHelper;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws Zend_Validate_Exception
     * @throws CouldNotSaveException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue('result');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            return $resultRedirect->setPath('webforms/form/');
        }
        $id          = (int)$this->getRequest()->getParam(ResultInterface::ID);
        $modelResult = $id ? $this->resultRepository->getById($id) :
            $this->webformResultFactory->create();
        $webformId   = $id ? $modelResult->getFormId() : (int)$data[FormInterface::ID];
        $customerId  = $this->getRequest()->getParam(ResultInterface::CUSTOMER_ID);
        if (!$webformId) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit',
                [ResultInterface::ID => $id, FormInterface::ID => $this->getRequest()->getParam(FormInterface::ID)]);
        }
        $form = $this->formRepository->getById($webformId);
        $form->setData('disable_captcha', true);
        $storeId = $data[ResultInterface::STORE_ID] ?: $modelResult->getStoreId();

        $this->_eventManager->dispatch(
            'webforms_fieldset_prepare_save',
            ['result' => $modelResult, 'form' => $form, 'request' => $this->getRequest()]
        );

        $result = $this->resultPostHelper->savePostResult($form,
            [
                'prefix' => 'result',
                self::ADMIN_SAVE => true,
            ]
        );

        // if we get validation error
        if (!$result) {
            if ($data[ResultInterface::ID]) {
                $resultId = $data[ResultInterface::ID];
                if ($customerId) {
                    return $resultRedirect->setPath('adminhtml/customer/edit',
                        ['id' => $customerId, 'tab' => 'webform_results']);
                }
                return $resultRedirect->setPath('*/*/edit', ['_current' => true, ResultInterface::ID => $resultId]);
            }
            return $resultRedirect->setPath('*/*/new', [FormInterface::ID => $webformId]);
        }

        if ($data[ResultInterface::CUSTOMER_ID]) {
            $result->setCustomerId($data[ResultInterface::CUSTOMER_ID]);
        }
        $result->setStoreId($storeId);
        $this->resultRepository->save($result);
        $this->messageManager->addSuccessMessage(__('Result was successfully saved'));

        if ($this->getRequest()->getParam(ResultInterface::CUSTOMER_ID)) {
            return $resultRedirect->setPath('customer/index/edit', [
                'id' => $this->getRequest()->getParam(ResultInterface::CUSTOMER_ID)
            ]);
        }

        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', [ResultInterface::ID => $result->getId()]);
        }
        if ($customerId) {
            return $resultRedirect->setPath('adminhtml/customer/edit',
                ['id' => $customerId, 'tab' => 'webform_results']);
        }
        return $resultRedirect->setPath('*/*/index', [FormInterface::ID => $webformId]);
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(ResultInterface::ID);
        $formId    = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($id) {
            $model  = $this->resultRepository->getById($id);
            $formId = $model->getFormId();
        }
        if ($formId && !$this->accessHelper->isAllowed($formId)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }
}
