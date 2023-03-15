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

namespace MageMe\WebForms\Controller\Adminhtml\Result\Customer;


use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Mail\ApprovalNotification;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory;
use MageMe\WebForms\Model\Result;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;

class MassStatus extends AbstractAjaxCustomerMassAction
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::edit_result';
    const ACTION = 'update';

    /**
     * @var ApprovalNotification
     */
    private $approvalNotification;

    public function __construct(
        ApprovalNotification      $approvalNotification,
        ResultRepositoryInterface $repository,
        CollectionFactory         $collectionFactory,
        Filter                    $filter,
        JsonFactory               $jsonFactory,
        Context                   $context
    )
    {
        parent::__construct($repository, $collectionFactory, $filter, $jsonFactory, $context);
        $this->approvalNotification = $approvalNotification;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    protected function action(AbstractDb $collection): Phrase
    {
        $newStatus = $this->getRequest()->getParam('status');

        /** @var Result $result */
        foreach ($collection as $result) {
            $result = $this->repository->getById($result->getId());
            $result->setApproved($newStatus);
            $this->repository->save($result);
            if ($result->getForm()->getIsApprovalNotificationEnabled()) {
                $this->approvalNotification->sendEmail($result);
            }

            $this->_eventManager->dispatch('webforms_result_approve', ['result' => $result]);
        }

        return __('A total of %1 record(s) have been updated.', $collection->getSize());
    }
}