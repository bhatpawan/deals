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


use Exception;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Controller\Adminhtml\AbstractMassAction;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Mail\ApprovalNotification;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory as CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class MassStatus extends AbstractMassAction
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::reply';
    const ID_FIELD = 'selected';
    const REDIRECT_URL = '*/*/';

    /**
     * @var array
     */
    protected $redirect_params = ['_current' => true];

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResultRepositoryInterface
     */
    protected $repository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var ApprovalNotification
     */
    protected $approvalNotification;

    /**
     * MassStatus constructor.
     * @param ApprovalNotification $approvalNotification
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param CollectionFactory $collectionFactory
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        ApprovalNotification      $approvalNotification,
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        CollectionFactory         $collectionFactory,
        AccessHelper              $accessHelper,
        Context                   $context)
    {
        parent::__construct($accessHelper, $context);
        $this->collectionFactory    = $collectionFactory;
        $this->repository           = $resultRepository;
        $this->formRepository       = $formRepository;
        $this->approvalNotification = $approvalNotification;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute()
    {
        $Ids    = $this->getIds();
        $status = (int)$this->getRequest()->getParam('status');
        $formId = (int)$this->getRequest()->getParam(ResultInterface::FORM_ID);
        if (empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($Ids as $id) {
                    $result = $this->repository->getById($id);
                    $result->setApproved($status);
                    $this->repository->save($result);

                    if ($result->getForm()->getIsApprovalNotificationEnabled()) {
                        $this->approvalNotification->sendEmail($result);
                    }

                    $this->_eventManager->dispatch('webforms_result_approve', ['result' => $result]);
                }
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 result(s) have been updated.', count($Ids))
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $customerId     = $this->getRequest()->getParam(ResultInterface::CUSTOMER_ID);
        if ($customerId) {
            return $resultRedirect->setPath('customer/index/edit', [
                'id' => $customerId
            ]);
        }
        return $resultRedirect->setPath(static::REDIRECT_URL, [ResultInterface::FORM_ID => $formId]);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function getIds(): array
    {
        if ($this->getRequest()->getParam('excluded') !== 'false') {
            return parent::getIds();
        }
        $Ids       = [];
        $webformId = (int)$this->getRequest()->getParam(ResultInterface::FORM_ID);
        if ($webformId) {
            $filters    = $this->getRequest()->getParam('filters');
            $collection = $this->collectionFactory->create();
            $collection->addFilter(ResultInterface::FORM_ID, $webformId);
            foreach ($filters as $fieldName => $value) {
                if (strstr((string)$fieldName, 'field_')) {
                    $fieldID = (int)str_replace('field_', '', (string)$fieldName);
                    $collection->addFieldFilter($fieldID, $value);
                }
            }
            if (isset($filters[ResultInterface::CREATED_AT])) {
                $from = $to = false;
                if (!empty($filters[ResultInterface::CREATED_AT]['from'])) {
                    $from = date('Y-m-d', strtotime($filters[ResultInterface::CREATED_AT]['from'])) . ' 00:00:00';
                }
                if (!empty($filters[ResultInterface::CREATED_AT]['to'])) {
                    $to = date('Y-m-d', strtotime($filters[ResultInterface::CREATED_AT]['to'])) . ' 23:59:59';
                }
                if ($from) {
                    $collection->addFieldToFilter(ResultInterface::CREATED_AT, ['gteq' => $from]);
                }
                if ($to) {
                    $collection->addFieldToFilter(ResultInterface::CREATED_AT, ['lteq' => $to]);
                }
            }
            if (isset($filters[ResultInterface::ID])) {
                $from = $to = false;
                if (!empty($filters[ResultInterface::ID]['from'])) {
                    $from = $filters[ResultInterface::ID]['from'];
                }
                if (!empty($filters[ResultInterface::ID]['to'])) {
                    $to = $filters[ResultInterface::ID]['to'];
                }
                if ($from) {
                    $collection->addFieldToFilter(ResultInterface::ID, ['gteq' => $from]);
                }
                if ($to) {
                    $collection->addFieldToFilter(ResultInterface::ID, ['lteq' => $to]);
                }
            }
            if (isset($filters[ResultInterface::APPROVED])) {
                $collection->addFilter(ResultInterface::APPROVED, $filters[ResultInterface::APPROVED]);
            }
            if (isset($filters['customer'])) {
                $collection->addFieldToFilter('customer', ['like' => '%' . $filters['customer'] . '%']);
            }
            foreach ($collection as $result) {
                $Ids[] = $result->getId();
            }
        }
        return $Ids;
    }
}
