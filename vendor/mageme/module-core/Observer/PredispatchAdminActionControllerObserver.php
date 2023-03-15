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

namespace MageMe\Core\Observer;

use MageMe\Core\Api\LicenseHelperInterface;
use MageMe\Core\Config\FeedFactory;
use MageMe\Core\Helper\LicenseHelper;
use MageMe\Core\Helper\ModulesHelper;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PredispatchAdminActionControllerObserver implements ObserverInterface
{
    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var LicenseHelper
     */
    protected $licenseHelper;

    /**
     * @var ModulesHelper
     */
    protected $modulesHelper;

    /**
     * @param FeedFactory $feedFactory
     * @param LicenseHelper $licenseHelper
     * @param ModulesHelper $modulesHelper
     * @param Session $backendAuthSession
     */
    public function __construct(
        FeedFactory   $feedFactory,
        LicenseHelper $licenseHelper,
        ModulesHelper $modulesHelper,
        Session       $backendAuthSession
    )
    {
        $this->feedFactory        = $feedFactory;
        $this->licenseHelper      = $licenseHelper;
        $this->modulesHelper      = $modulesHelper;
        $this->backendAuthSession = $backendAuthSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            $feed = $this->feedFactory->create();
            $feed->checkUpdate();

            $magemeModules = $this->modulesHelper->getModules();
            foreach ($magemeModules as $module) {
                $moduleLicenseHelper = $this->modulesHelper->getModuleLicenseHelper($module);
                if ($moduleLicenseHelper instanceof LicenseHelperInterface)
                    $moduleLicenseHelper->verifyLicense();
            }
        }
    }
}
