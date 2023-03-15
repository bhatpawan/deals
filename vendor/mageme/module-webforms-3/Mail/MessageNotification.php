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

namespace MageMe\WebForms\Mail;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Helper\MailHelper;
use MageMe\WebForms\Model\FileMessage;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Mime;

class MessageNotification
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var MailHelper
     */
    protected $mailHelper;

    /**
     * MessageNotification constructor.
     * @param MailHelper $mailHelper
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        MailHelper            $mailHelper,
        StoreManagerInterface $storeManager,
        TransportBuilder      $transportBuilder
    )
    {
        $this->mailHelper       = $mailHelper;
        $this->storeManager     = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param MessageInterface $message
     * @param string $email
     * @param string $cc
     * @param string $bcc
     * @return bool
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmail(MessageInterface $message, string $email, string $cc, string $bcc): bool
    {
        if (!$email) {
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $result  = $message->getResult();
        $storeId = $result->getStoreId();
        $form    = $result->getForm();

        $sender = [
            'name' => $this->storeManager->getStore($storeId)->getFrontendName(),
            'email' => $this->mailHelper->getReplyToForCustomer($result),
        ];

        if (strlen(trim((string)$form->getCustomerNotificationSenderName())) > 0) {
            $sender['name'] = $form->getCustomerNotificationSenderName();
        }

        if ($this->storeManager->getStore($storeId)->getConfig('webforms/email/email_from')) {
            $sender['email'] = $this->storeManager->getStore($storeId)->getConfig('webforms/email/email_from');
        }

        $vars       = $message->getTemplateVars();
        $templateId = 'webforms_reply';

        if ($form->getEmailReplyTemplateId()) {
            $templateId = $form->getEmailReplyTemplateId();
        }

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($vars)
            ->setFromByScope($sender)
            ->addTo($email)
            ->setReplyTo($this->mailHelper->getReplyToForCustomer($result));

        /** Add attachments */
        $attachedFiles = $this->prepareAttachments($message);

        /** @var FileMessage $file */
        foreach ($attachedFiles as $file) {
            $transport->createAttachment(
                $file->getData(TransportBuilder::ATTACHMENT_BODY) ?? file_get_contents($file->getFullPath()),
                $file->getData(TransportBuilder::ATTACHMENT_TYPE) ?? Zend_Mime::TYPE_OCTETSTREAM,
                $file->getData(TransportBuilder::ATTACHMENT_DISPOSITION) ?? Zend_Mime::DISPOSITION_ATTACHMENT,
                $file->getData(TransportBuilder::ATTACHMENT_ENCODING) ?? Zend_Mime::ENCODING_BASE64,
                $file->getName(),
                $file->getData(TransportBuilder::ATTACHMENT_ID)
            );
        }

        /** Add Cc */
        $ccList = explode(',', $cc);
        $ccList = array_map('trim', $ccList);
        if (is_array($ccList)) {
            foreach ($ccList as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    $transport->addCc($cc);
                }
            }
        }

        /** Add Bcc */
        $bccList = explode(',', $bcc);
        $bccList = array_map('trim', $bccList);
        if (is_array($bccList)) {
            foreach ($bccList as $bcc) {
                if (filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
                    $transport->addBcc($bcc);
                }
            }
        }

        $transport->getTransport()->sendMessage();

        return true;
    }

    /**
     * @param MessageInterface $message
     * @return array
     */
    public function prepareAttachments(MessageInterface $message): array {
        $attachments = [];
        try {
            foreach ($message->getFiles() as $file) {
                $attachments[] = $file;
            }
        } catch (LocalizedException $e) {
        }
        return $attachments;
    }
}
