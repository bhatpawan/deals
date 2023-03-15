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


use MageMe\Core\Helper\ModulesHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class AbstractAction extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var ModulesHelper
     */
    protected $modulesHelper;

    /**
     * AbstractLicense constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ModulesHelper $modulesHelper
     */
    public function __construct(
        Context       $context,
        JsonFactory   $jsonFactory,
        ModulesHelper $modulesHelper
    )
    {
        parent::__construct($context);
        $this->jsonFactory   = $jsonFactory;
        $this->modulesHelper = $modulesHelper;
    }
}
