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
use MageMe\WebForms\Api\Mail\NotificationInterface;
use MageMe\WebForms\Helper\MailHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Mime;

abstract class AbstractNotification implements NotificationInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var MailHelper
     */
    protected $mailHelper;

    /**
     * @var DateHelper
     */
    private $dateHelper;

    /**
     * AbstractMail constructor.
     * @param MailHelper $mailHelper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param TransportBuilder $transportBuilder
     * @param DateHelper $dateHelper
     * @param string $template
     */
    public function __construct(
        MailHelper            $mailHelper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface  $scopeConfig,
        TimezoneInterface     $timezone,
        TransportBuilder      $transportBuilder,
        DateHelper            $dateHelper,
        string                $template = ''
    )
    {
        $this->scopeConfig      = $scopeConfig;
        $this->storeManager     = $storeManager;
        $this->timezone         = $timezone;
        $this->transportBuilder = $transportBuilder;
        $this->template         = $template;
        $this->mailHelper       = $mailHelper;
        $this->dateHelper       = $dateHelper;
    }

    /**
     * @param ResultInterface $result
     * @param array|null $to
     */
    abstract public function sendEmail(ResultInterface $result, ?array $to = null);

    /**
     * @param ResultInterface $result
     * @return array
     */
    public function prepareAttachments(ResultInterface $result): array
    {
        return [];
    }

    /**
     * @return TransportBuilder
     */
    public function getTransportBuilder(): TransportBuilder
    {
        return $this->transportBuilder;
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    public function getVarsForSendEmail(ResultInterface $result): array
    {
        $vars                    = $result->getTemplateVars();
        $vars['webform_subject'] = $this->getEmailSubject($result);
        $vars['recipient']       = $this->template;
        $vars['timestamp']       = $this->dateHelper->formatDate($result->getCreatedAt(), $result->getStoreId());

        return $vars;
    }

    /**
     * @param ResultInterface $result
     * @return Phrase|string
     */
    abstract public function getEmailSubject(ResultInterface $result);

    /**
     * @param ResultInterface $result
     * @param array $variables
     * @param array $sender
     * @param string $templateId
     * @param string $email
     * @param array $bccList
     * @param array $attachedFiles
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function prepareTransportBuilder(
        ResultInterface $result,
        array           $variables,
        array           $sender,
        string          $templateId,
        string          $email,
        array           $bccList = [],
        array           $attachedFiles = []
    )
    {
        @$this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setParts([])
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($variables)
            ->setFromByScope($sender)
            ->addTo($email)
            ->setReplyTo($this->mailHelper->getReplyToForAdmin($result), $sender['name']);

        // attach file content
        foreach ($attachedFiles as $file) {
            @$this->transportBuilder->createAttachment(
                $file->getData(TransportBuilder::ATTACHMENT_BODY) ?? file_get_contents($file->getFullPath()),
                $file->getData(TransportBuilder::ATTACHMENT_TYPE) ?? Zend_Mime::TYPE_OCTETSTREAM,
                $file->getData(TransportBuilder::ATTACHMENT_DISPOSITION) ?? Zend_Mime::DISPOSITION_ATTACHMENT,
                $file->getData(TransportBuilder::ATTACHMENT_ENCODING) ?? Zend_Mime::ENCODING_BASE64,
                $file->getName(),
                $file->getData(TransportBuilder::ATTACHMENT_ID)
            );
        }
        if (is_array($bccList)) {
            foreach ($bccList as $bcc) {
                if (filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
                    @$this->transportBuilder->addBcc($bcc);
                }
            }
        }
    }
}
