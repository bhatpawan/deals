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

namespace MageMe\WebForms\Controller\Adminhtml\Message;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Mail\MessageNotification;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;

class Email extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var MessageNotification
     */
    protected $messageNotification;

    /**
     * Email constructor.
     * @param MessageNotification $messageNotification
     * @param ResultRepositoryInterface $resultRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param AccessHelper $accessHelper
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        MessageNotification        $messageNotification,
        ResultRepositoryInterface  $resultRepository,
        MessageRepositoryInterface $messageRepository,
        AccessHelper               $accessHelper,
        JsonFactory                $resultJsonFactory,
        Context                    $context
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->accessHelper        = $accessHelper;
        $this->messageRepository   = $messageRepository;
        $this->resultRepository    = $resultRepository;
        $this->messageNotification = $messageNotification;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws MailException
     */
    public function execute()
    {
        $id                = (int)$this->getRequest()->getParam(MessageInterface::ID);
        $email             = (string)$this->getRequest()->getParam('email');
        $cc                = (string)$this->getRequest()->getParam('cc');
        $bcc               = (string)$this->getRequest()->getParam('bcc');
        $result['success'] = false;
        if ($id) {
            $message           = $this->messageRepository->getById($id);
            $result['success'] = $this->messageNotification->sendEmail($message, $email, $cc, $bcc);
            if ($result['success']) {
                $message->setIsCustomerEmailed(true);
                $this->messageRepository->save($message);
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData(json_encode($result));
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $id = (int)$this->getRequest()->getParam(MessageInterface::ID);
        if (!$id) return false;
        $message = $this->messageRepository->getById($id);
        $result  = $this->resultRepository->getById($message->getResultId());
        return $this->accessHelper->isAllowed($result->getFormId());
    }
}
