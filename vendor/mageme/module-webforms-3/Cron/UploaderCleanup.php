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

namespace MageMe\WebForms\Cron;

use MageMe\WebForms\File\CustomerNotificationUploader;
use MageMe\WebForms\File\DropzoneUploader;

class UploaderCleanup
{
    /**
     * @var DropzoneUploader
     */
    protected $dropzoneUploader;
    /**
     * @var CustomerNotificationUploader
     */
    protected $customerNotificationUploader;

    /**
     * UploaderCleanup constructor.
     * @param CustomerNotificationUploader $customerNotificationUploader
     * @param DropzoneUploader $dropzoneUploader
     */
    public function __construct(
        CustomerNotificationUploader $customerNotificationUploader,
        DropzoneUploader             $dropzoneUploader
    )
    {
        $this->dropzoneUploader             = $dropzoneUploader;
        $this->customerNotificationUploader = $customerNotificationUploader;
    }

    public function execute()
    {
        $this->dropzoneUploader->cleanupTmp();
        $this->customerNotificationUploader->cleanupTmp();
    }
}
