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

namespace MageMe\WebForms\Controller\Adminhtml\Result;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class Messages extends Action
{
    protected $resultPageFactory;
    protected $layoutFactory;

    public function __construct(
        LayoutFactory $layoutFactory,
        RawFactory    $resultPageFactory,
        Context       $context)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->layoutFactory     = $layoutFactory;
    }

    public function execute()
    {
        $layout = $this->layoutFactory->create();
        $layout->getUpdate()->addHandle('webforms_result_messages');
        $layout->generateXml();
        return $this->resultPageFactory->create()->setContents(
            $layout->getOutput()
        );
    }
}
