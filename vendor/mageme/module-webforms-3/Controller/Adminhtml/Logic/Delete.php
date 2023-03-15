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

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::save_form';

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Delete constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param LogicRepositoryInterface $logicRepository
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        LogicRepositoryInterface $logicRepository,
        AccessHelper             $accessHelper,
        Context                  $context)
    {
        parent::__construct($context);
        $this->accessHelper    = $accessHelper;
        $this->logicRepository = $logicRepository;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id             = (int)$this->getRequest()->getParam(LogicInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {

            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find a logic to delete.'));

            // go to grid
            return $resultRedirect->setPath('*/form/');
        }
        try {
            $model = $this->logicRepository->getById($id);
            $this->logicRepository->delete($model);

            // display success message
            $this->messageManager->addSuccessMessage(__('The logic has been deleted.'));
            $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
            if ($formId) {
                return $resultRedirect->setPath('*/form/edit',
                    [FormInterface::ID => $formId]);
            }
            return $resultRedirect->setPath('*/field/edit', [LogicInterface::FIELD_ID => $model->getFieldId()]);
        } catch (Exception $e) {

            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());

            // go back to edit logic
            return $resultRedirect->setPath('*/*/edit', [LogicInterface::ID => $id]);
        }
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(LogicInterface::ID);
        if ($id) {
            $fieldId = $this->logicRepository->getById($id)->getFieldId();
            if ($fieldId) {
                $field  = $this->fieldRepository->getById($fieldId);
                $formId = $field->getFormId();
                if ($formId && !$this->accessHelper->isAllowed($formId)) {
                    $isAllowed = false;
                }
            }
        }
        return $isAllowed;
    }
}
