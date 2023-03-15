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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action
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
     * Delete constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param AccessHelper $accessHelper
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        ResultRepositoryInterface  $resultRepository,
        MessageRepositoryInterface $messageRepository,
        AccessHelper               $accessHelper,
        JsonFactory                $resultJsonFactory,
        Context                    $context
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accessHelper      = $accessHelper;
        $this->messageRepository = $messageRepository;
        $this->resultRepository  = $resultRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function execute()
    {
        $id                = (int)$this->getRequest()->getParam(MessageInterface::ID);
        $result['success'] = false;
        if ($id) {
            $message           = $this->messageRepository->getById($id);
            $result['success'] = $this->messageRepository->delete($message);
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