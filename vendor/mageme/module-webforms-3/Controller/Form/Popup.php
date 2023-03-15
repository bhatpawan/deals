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

namespace MageMe\WebForms\Controller\Form;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Block\Form;
use MageMe\WebForms\Block\Widget\Button;
use MageMe\WebForms\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\HttpFactory;
use Magento\Framework\View\Result\PageFactory;

class Popup extends AbstractAction
{
    /**
     * @var HttpFactory
     */
    protected $httpFactory;

    public function __construct(HttpFactory $httpFactory, Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context, $pageFactory);
        $this->httpFactory = $httpFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        $containerId = $this->request->getParam(Button::CONTAINER_ID);
        $popupCssClass = $this->request->getParam(Button::POPUP_CSS_CLASS);
        $popupTitle = $this->request->getParam(Button::POPUP_TITLE);

        $resultHttp = $this->httpFactory->create();
        $resultHttp->setNoCacheHeaders();
        $resultHttp->setHeader('Content-Type', 'text/html', true);
        $resultPage = $this->pageFactory->create();
        $html = "<div id='$containerId' class='webforms-popup $popupCssClass'>";
        if ($popupTitle) {
            $html .= "<h2 class='webforms-popup-title'>$popupTitle</h2>";
        }
        $html .= $resultPage->getLayout()->createBlock(Form::class,
            null,
            [
                'data' => [
                    FormInterface::ID => $formId
                ]
            ]
        )->toHtml();
        $html .= "</div>";
        return $resultHttp->setContent($html);
    }
}