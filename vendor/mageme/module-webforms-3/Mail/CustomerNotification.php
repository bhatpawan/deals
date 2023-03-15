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

use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Helper\MailHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomerNotification extends AbstractNotification
{
    /**
     * @var FileCustomerNotificationRepositoryInterface
     */
    private $fileCustomerNotificationRepository;

    /**
     * CustomerNotification constructor.
     * @param FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository
     * @param MailHelper $mailHelper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param TransportBuilder $transportBuilder
     * @param DateHelper $dateHelper
     * @param string $template
     */
    public function __construct(
        FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository,
        MailHelper                                  $mailHelper,
        StoreManagerInterface                       $storeManager,
        ScopeConfigInterface                        $scopeConfig,
        TimezoneInterface                           $timezone,
        TransportBuilder                            $transportBuilder,
        DateHelper                                  $dateHelper,
        string                                      $template = ''
    )
    {
        parent::__construct($mailHelper, $storeManager, $scopeConfig, $timezone, $transportBuilder, $dateHelper, $template);
        $this->fileCustomerNotificationRepository = $fileCustomerNotificationRepository;
    }

    /**
     * @inheritdoc
     */
    public function sendEmail(ResultInterface $result, ?array $to = null)
    {
        $form         = $result->getForm();
        $templateId   = $this->template;
        $replyTo      = $this->mailHelper->getReplyToForCustomer($result);
        $sender       = [
            'email' => $replyTo,
        ];

        $variables = $this->getVarsForSendEmail($result);

        $senderName = $this->storeManager->getStore($result->getStoreId())->getFrontendName();
        if (strlen(trim($form->getCustomerNotificationSenderName())) > 0) {
            $senderName = $form->getCustomerNotificationSenderName();
        }
        $sender['name'] = $senderName;

        $email = $result->getCustomerEmail();

        if ($form->getCustomerNotificationTemplateId()) {
            $templateId = $form->getCustomerNotificationTemplateId();
        }

        $bccList = $form->getCustomerNotificationBcc() ? explode(',', $form->getCustomerNotificationBcc()) : [];

        if ($this->scopeConfig->getValue(self::CONFIG_EMAIL_FROM, $result->getScope())) {
            $sender['email'] = $this->scopeConfig->getValue(self::CONFIG_EMAIL_FROM, $result->getScope());
        }

        // trim bcc array
        array_walk($bccList, 'trim');

        $attachedFiles = $this->prepareAttachments($result);

        if (count($email)) {
            $emailList = $email;
            array_walk($emailList, 'trim');
            foreach ($emailList as $email) {
                $this->prepareTransportBuilder($result, $variables,
                    $sender, $templateId, $email, $bccList, $attachedFiles);
                $this->getTransportBuilder()->setReplyTo($this->mailHelper->getReplyToForCustomer($result), $sender['name']);

                @$this->transportBuilder->getTransport()->sendMessage();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function prepareAttachments(ResultInterface $result): array
    {
        $attachments = parent::prepareAttachments($result);
        $form        = $result->getForm();

        // Add custom attachments
        foreach ($form->getCustomerNotificationAttachments() as $attachment) {
            try {
                $attachments[] = $this->fileCustomerNotificationRepository->getById($attachment['id']);
            } catch (NoSuchEntityException $e) {
            }
        }
        if ($form->getIsCustomerNotificationAttachmentEnabled()) {
            foreach ($result->getFiles() as $file) {
                $attachments[] = $file;
            }
        }
        return $attachments;
    }

    public function getEmailSubject(ResultInterface $result)
    {
        $form       = $result->getForm();
        $store_name = $this->storeManager->getStore($result->getStoreId())->getFrontendName();

        return __("You have submitted [%1] form on %2 website", $form->getName(), $store_name)->render();
    }
}
