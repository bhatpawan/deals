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

use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\UnreadFactory as MessageList;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Message\Factory as MessageFactory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var MessageList
     */
    protected $messageList;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param PageFactory $resultPageFactory
     * @param MessageFactory $messageFactory
     * @param MessageList $messageList
     * @param Context $context
     */
    public function __construct(
        PageFactory    $resultPageFactory,
        MessageFactory $messageFactory,
        MessageList    $messageList,
        Context        $context)
    {
        parent::__construct($context);
        $this->messageList       = $messageList;
        $this->messageFactory    = $messageFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        foreach ($this->messageList->create() as $message) {
            if (strstr((string)$message->getData('url'), 'webforms-pro-m2')) {
                $link       = '<a href="' . $message->getData('url') . '" target="_blank">' . __('Read Details') . '</a>';
                $markAsRead = '<a class="mageme-mark-as-read" href="' . $this->getUrl('adminhtml/notification/markAsRead',
                        ['id' => $message->getId()]) . '">' . __('Mark as Read') . '</a>';
                $text       = $message->getData('title') . ' ' . $link . $markAsRead;
                $this->messageManager->addMessage($this->messageFactory->create(MessageInterface::TYPE_NOTICE, $text));
            }
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMe_WebForms::manage_forms');
        $resultPage->addBreadcrumb(__('WebForms'), __('WebForms'));
        $resultPage->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Forms'));

        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}
