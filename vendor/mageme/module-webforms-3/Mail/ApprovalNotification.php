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
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;

class ApprovalNotification extends AbstractNotification
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
        $emailList  = $result->getCustomerEmail();

        $sender = [
            'name' => trim($form->getCustomerNotificationSenderName()) ? $form->getCustomerNotificationSenderName()
                : $this->storeManager->getStore($result->getStoreId())->getFrontendName(),
            'email' => $this->mailHelper->getReplyToForCustomer($result),
        ];
        if ($this->scopeConfig->getValue(self::CONFIG_EMAIL_FROM, $result->getScope(), $result->getStoreId())) {
            $sender['email'] = $this->scopeConfig->getValue(self::CONFIG_EMAIL_FROM, $result->getScope(), $result->getStoreId());
        }

        $variables = $this->getVarsForSendEmail($result);
        $bccList   = explode(',', $form->getApprovalNotificationBcc());

        // trim bcc array
        array_walk($bccList, 'trim');

        switch ($result->getApproved()) {
            case ApprovalStatus::STATUS_APPROVED:
            {
                if ($form->getApprovalNotificationApprovedTemplateId()) {
                    $templateId = $form->getApprovalNotificationApprovedTemplateId();
                }
                break;
            }
            case ApprovalStatus::STATUS_NOT_APPROVED:
            {
                if ($form->getApprovalNotificationNotapprovedTemplateId()) {
                    $templateId = $form->getApprovalNotificationNotapprovedTemplateId();
                }
                break;
            }
            case ApprovalStatus::STATUS_COMPLETED:
            {
                if ($form->getApprovalNotificationCompletedTemplateId()) {
                    $templateId = $form->getApprovalNotificationCompletedTemplateId();
                }
                break;
            }
            default:
            {
                return;
            }
        }
        foreach ($emailList as $email) {
            $this->prepareTransportBuilder($result, $variables,
                $sender, $templateId, $email, $bccList);
            $this->getTransportBuilder()->setReplyTo($this->mailHelper->getReplyToForCustomer($result), $sender['name']);

            @$this->transportBuilder->getTransport()->sendMessage();
        }
    }

    /**
     * @inheritdoc
     */
    public function getVarsForSendEmail(ResultInterface $result): array
    {
        $vars = $result->getTemplateVars();
        $vars['status'] = $result->getStatusName();
        $vars['result']->addData([
            'id' => $result->getId(),
            'date' => $this->timezone->formatDate($result->getCreatedAt()),
            'html' => $result->toHtml(),
        ]);
        return $vars;
    }

    /**
     * @inheritDoc
     */
    public function getEmailSubject(ResultInterface $result)
    {
        $form = $result->getForm();
        return __('Your [%1] submission status has changed', $form->getName());
    }
}
