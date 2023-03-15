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

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Api\TmpFileMessageRepositoryInterface;
use MageMe\WebForms\File\MessageUploader;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Mail\MessageNotification;
use MageMe\WebForms\Model\MessageFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var TmpFileMessageRepositoryInterface
     */
    protected $tmpFileMessageRepository;

    /**
     * @var MessageUploader
     */
    protected $uploader;

    /**
     * @var MessageNotification
     */
    protected $messageNotification;

    /**
     * Save constructor.
     * @param MessageNotification $messageNotification
     * @param MessageUploader $uploader
     * @param TmpFileMessageRepositoryInterface $tmpFileMessageRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param MessageFactory $messageFactory
     * @param AccessHelper $accessHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterProvider $filterProvider
     * @param Session $authSession
     * @param Context $context
     */
    public function __construct(
        MessageNotification               $messageNotification,
        MessageUploader                   $uploader,
        TmpFileMessageRepositoryInterface $tmpFileMessageRepository,
        ResultRepositoryInterface         $resultRepository,
        MessageRepositoryInterface        $messageRepository,
        MessageFactory                    $messageFactory,
        AccessHelper                      $accessHelper,
        ScopeConfigInterface              $scopeConfig,
        FilterProvider                    $filterProvider,
        Session                           $authSession,
        Context                           $context
    )
    {
        parent::__construct($context);
        $this->authSession              = $authSession;
        $this->filterProvider           = $filterProvider;
        $this->scopeConfig              = $scopeConfig;
        $this->accessHelper             = $accessHelper;
        $this->messageFactory           = $messageFactory;
        $this->messageRepository        = $messageRepository;
        $this->resultRepository         = $resultRepository;
        $this->tmpFileMessageRepository = $tmpFileMessageRepository;
        $this->uploader                 = $uploader;
        $this->messageNotification      = $messageNotification;
    }

    /**
     * @inheritDoc
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function execute()
    {
        $result_id      = (int)$this->getRequest()->getParam(MessageInterface::RESULT_ID);
        $customerId     = (int)$this->getRequest()->getParam('customer_id');
        $email          = is_array($this->getRequest()->getParam('email')) ?
            $this->getRequest()->getParam('email')[0] :
            (string)$this->getRequest()->getParam('email');
        $cc             = (string)$this->getRequest()->getParam('cc');
        $bcc            = (string)$this->getRequest()->getParam('bcc');
        $attachment     = (string)$this->getRequest()->getParam('attachment');
        $sendEmail      = (bool)$this->getRequest()->getParam('send_email');
        $messageContent = (string)$this->getRequest()->getParam(MessageInterface::MESSAGE);

        $user   = $this->authSession->getUser();
        $filter = $this->filterProvider->getPageFilter();
        $result = $this->resultRepository->getById($result_id);

        $message = $this->messageFactory->create()
            ->setAuthor($user->getName())
            ->setUserId($user->getId())
            ->setResultId($result_id);
        $message = $this->messageRepository->save($message);
        if ($attachment) {
            $this->saveAttachedFiles($message, $attachment);
        }

        // add template processing
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $filter->setStoreId($result->getStoreId());
        $filter->setVariables($message->getTemplateVars());
        if (!empty($messageContent)) {
            $messageContent = $filter->filter($messageContent);
        }
        if ($this->scopeConfig->getValue('webforms/message/nl2br')) {
            $messageContent = str_replace("</p><br>", "</p>", nl2br($messageContent, true));
        }
        $message->setMessage($messageContent);

        $emailed = false;
        if ($sendEmail && $email) {
            try {
                $emailed = $this->messageNotification->sendEmail($message, $email, $cc, $bcc);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
            $message->setIsCustomerEmailed($emailed);
        }

        try {
            $this->messageRepository->save($message);
            $this->resultRepository->save($result->setIsReplied(true));
            $this->messageManager->addSuccessMessage(__('The reply has been saved.'));
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        if ($emailed) {
            $this->messageManager->addSuccessMessage(__('The reply has been emailed.'));
        } elseif ($sendEmail) {

            /** @noinspection SpellCheckingInspection */
            $this->messageManager->addErrorMessage(__('The result doesn\'t have reply-to e-mail address.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($customerId) {
            return $resultRedirect->setPath('adminhtml/customer/edit',
                ['id' => $customerId, 'tab' => 'webform_results']);
        }

        return $resultRedirect->setPath('*/result/', [FormInterface::ID => $result->getFormId()]);
    }

    /**
     * @param MessageInterface $message
     * @param string $attachment
     * @throws Exception
     */
    protected function saveAttachedFiles(MessageInterface $message, string $attachment)
    {
        $hashArray = explode(';', $attachment);
        if (!$hashArray || !is_array($hashArray)) {
            return;
        }
        foreach ($hashArray as $hash) {
            $tmpFile = $this->tmpFileMessageRepository->getByHash($hash);
            if ($tmpFile->getId()) {
                $this->uploader->copyFileFromTmpDir($tmpFile, $message);
                $this->tmpFileMessageRepository->delete($tmpFile);
            }
        }
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $result_id = $this->getRequest()->getParam(MessageInterface::RESULT_ID);
        if (!$result_id) return false;
        $result = $this->resultRepository->getById($result_id);
        return $this->accessHelper->isAllowed($result->getFormId());
    }
}
