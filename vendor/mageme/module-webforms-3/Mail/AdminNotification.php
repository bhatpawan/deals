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

use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class AdminNotification extends AbstractNotification
{
    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmail(ResultInterface $result, ?array $to = null)
    {
        $form       = $result->getForm();
        $templateId = $this->template;
        $email      = false;
        $name       = $form->getAdminNotificationSenderName() ?: $result->getCustomerName();
        $replyTo    = $this->mailHelper->getReplyToForAdmin($result);
        $sender     = [
            'name' => $name ?: $replyTo,
            'email' => $replyTo,
        ];

        if (!empty($to['email'])) {
            $email = (string)$to['email'];
        }

        $variables = $this->getVarsForSendEmail($result);

        $email = $email ?: (string)$form->getAdminNotificationEmail();
        if ($form->getAdminNotificationTemplateId()) {
            $templateId = $form->getAdminNotificationTemplateId();
        }
        $isSendMultipleAdmin = (is_string($email) && strstr($email, ','));
        $bccList             = explode(',', (string)$form->getAdminNotificationBcc());

        if ($form->getAdminNotificationSenderEmail()) {
            $sender['email'] = (string)$form->getAdminNotificationSenderEmail();
        }
        if ($this->scopeConfig->getValue(self::CONFIG_EMAIL_FROM, $result->getScope())) {
            $sender['email'] = $this->scopeConfig->getValue(self::CONFIG_EMAIL_FROM, $result->getScope());
        }

        // trim bcc array
        array_walk($bccList, 'trim');

        $attachedFiles = $this->prepareAttachments($result);

        if ($isSendMultipleAdmin) {
            $emailArray = array_map('trim', explode(',', (string)$email));
            foreach ($emailArray as $email) {
                if (!empty($email)) {
                    $this->prepareTransportBuilder($result, $variables,
                        $sender, $templateId, $email, $bccList, $attachedFiles);
                    @$this->transportBuilder->getTransport()->sendMessage();
                }
            }
        } else {
            $emailList = $email;
            if (!is_array($email)) {
                $emailList = [$email];
            }
            array_walk($emailList, 'trim');
            foreach ($emailList as $email) {
                if (!empty($email)) {
                    $this->prepareTransportBuilder($result, $variables,
                        $sender, $templateId, $email, $bccList, $attachedFiles);
                    @$this->transportBuilder->getTransport()->sendMessage();
                }
            }
        }
    }

    /**
     * @param ResultInterface $result
     * @return Phrase|string
     */
    public function getEmailSubject(ResultInterface $result)
    {
        return $result->getSubject();
    }

    /**
     * @inheritdoc
     */
    public function prepareAttachments(ResultInterface $result): array
    {
        $attachments = parent::prepareAttachments($result);
        $form = $result->getForm();
        if ($form->getIsAdminNotificationAttachmentEnabled()) {
            foreach ($result->getFiles() as $file) {
                $attachments[] = $file;
            }
        }
        return $attachments;
    }
}
