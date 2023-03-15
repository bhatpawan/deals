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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Mail\ApprovalNotification;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;

class SetStatus extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::edit_result';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var ApprovalNotification
     */
    protected $approvalNotification;

    /**
     * SetStatus constructor.
     * @param ApprovalNotification $approvalNotification
     * @param ResultRepositoryInterface $resultRepository
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        ApprovalNotification      $approvalNotification,
        ResultRepositoryInterface $resultRepository,
        JsonFactory               $resultJsonFactory,
        Context                   $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->resultRepository     = $resultRepository;
        $this->approvalNotification = $approvalNotification;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws MailException
     */
    public function execute()
    {
        $id     = (int)$this->getRequest()->getParam(ResultInterface::ID);
        $status = (int)$this->getRequest()->getParam('status');
        $result = $this->resultRepository->getById($id);
        $result->setApproved($status);
        $this->resultRepository->save($result);
        $modelForm = $result->getForm();

        if ($modelForm->getIsApprovalNotificationEnabled()) {
            $this->approvalNotification->sendEmail($result);
        }

        $this->_eventManager->dispatch('webforms_result_approve', ['result' => $result]);

        $response = [
            'text' => $result->getStatusName(),
            'status' => $result->getApproved()
        ];
        return $this->resultJsonFactory->create()->setJsonData(json_encode($response));
    }
}
