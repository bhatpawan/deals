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

namespace MageMe\Core\Controller\Adminhtml\License;


class Deactivate extends AbstractAction
{
    /**
     * @inheirtDoc
     */
    public function execute()
    {
        $moduleName    = $this->getRequest()->getParam('module_name');
        $licenseHelper = $this->modulesHelper->getModuleLicenseHelper($moduleName);
        $result        = $licenseHelper->deactivateLicense();
        $resultJson    = $this->jsonFactory->create();
        return $resultJson->setData($result);
    }
}
