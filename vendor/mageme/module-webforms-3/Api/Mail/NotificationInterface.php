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

namespace MageMe\WebForms\Api\Mail;

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Mail\TransportBuilder;
use Magento\Framework\Phrase;

interface NotificationInterface
{
    const CONFIG_EMAIL_FROM = 'webforms/email/email_from';

    /**
     * @param ResultInterface $result
     * @return Phrase|string
     */
    public function getEmailSubject(ResultInterface $result);

    /**
     * @param ResultInterface $result
     * @param ?array $to
     * @return void
     */
    public function sendEmail(ResultInterface $result, array $to = null);

    /**
     * @param ResultInterface $result
     * @return array
     */
    public function getVarsForSendEmail(ResultInterface $result): array;

    /**
     * @param ResultInterface $result
     * @param array $variables
     * @param array $sender
     * @param string $templateId
     * @param string $email
     * @param array $bccList
     * @param array $attachedFiles
     * @return void
     */
    public function prepareTransportBuilder(
        ResultInterface $result,
        array           $variables,
        array           $sender,
        string          $templateId,
        string          $email,
        array           $bccList = [],
        array           $attachedFiles = []
    );

    /**
     * @return TransportBuilder
     */
    public function getTransportBuilder(): TransportBuilder;
}
