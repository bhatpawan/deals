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
use MageMe\WebForms\Helper\Form\ExportHelper as ExportFormHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Export extends Action
{
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;
    /**
     * @var ExportFormHelper
     */
    protected $exportFormHelper;

    /**
     * Export constructor.
     * @param ExportFormHelper $exportFormHelper
     * @param FormRepositoryInterface $formRepository
     * @param Context $context
     */
    public function __construct(
        ExportFormHelper        $exportFormHelper,
        FormRepositoryInterface $formRepository,
        Context                 $context)
    {
        parent::__construct($context);
        $this->formRepository   = $formRepository;
        $this->exportFormHelper = $exportFormHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id             = (int)$this->getRequest()->getParam(FormInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Form identifier is not specified.'));
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $model = $this->formRepository->getById($id);
            $body  = $this->exportFormHelper->convertToJson($model);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }

        $fileName    = $model->getName() . '.json';
        $contentType = 'application/json';

        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if (strlen((string)$body)) {
            $this->getResponse()->setHeader('Content-Length', strlen((string)$body));
        }

        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $this->_getSession()->writeClose();

        return $this->getResponse()->setBody($body);
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}
