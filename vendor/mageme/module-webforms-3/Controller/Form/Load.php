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


use MageMe\WebForms\Block\Form;
use MageMe\WebForms\Block\SlideOutForm;
use MageMe\WebForms\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\HttpFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Load
 * @package MageMe\WebForms\Controller\Form
 */
class Load extends AbstractAction
{
    const FORM_ID = 'form_id';
    const CURRENT_URL = 'current_url';
    const FORM_LOADED = 'form_loaded'; // for async form
    const FOCUS = 'focus';
    const SLIDER_POSITION = 'slider_position';
    const BUTTON_TEXT = 'button_text';
    const BUTTON_COLOR = 'button_color';
    const BUTTON_TEXT_COLOR = 'button_text_color';
    const BACKGROUND_COLOR = 'background_color';
    const BORDER_COLOR = 'border_color';
    const FORM_WIDTH = 'form_width';
    const FORM_MARGIN_BOTTOM = 'form_margin_bottom';
    const IS_SLIDE_OUT = SlideOutForm::IS_SLIDE_OUT;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var HttpFactory
     */
    protected $httpFactory;

    /**
     * Load constructor.
     * @param Context $context
     * @param HttpFactory $httpFactory
     * @param Registry $registry
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context     $context,
        HttpFactory $httpFactory,
        Registry    $registry,
        PageFactory $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->registry    = $registry;
        $this->httpFactory = $httpFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultHttp = $this->httpFactory->create();
        $resultHttp->setNoCacheHeaders();
        $resultHttp->setHeader('Content-Type', 'text/html', true);
        $url = $this->request->getPost(self::CURRENT_URL);
        if ($url) {
            $this->registry->register(self::CURRENT_URL, $url, true);
        }
        $page  = $this->pageFactory->create();
        $blockClass = $this->getRequest()->getPost(self::IS_SLIDE_OUT) ? SlideOutForm::class : Form::class;
        $block = $page->getLayout()->createBlock($blockClass, null, [
            'data' => [
                self::FORM_ID => $this->request->getPost(self::FORM_ID),
                self::FORM_LOADED => $this->request->getPost(self::FORM_LOADED),
                self::FOCUS => $this->request->getPost(self::FOCUS),
                self::SLIDER_POSITION => $this->request->getPost(self::SLIDER_POSITION),
                self::BUTTON_TEXT => $this->request->getPost(self::BUTTON_TEXT),
                self::BUTTON_COLOR => $this->request->getPost(self::BUTTON_COLOR),
                self::BUTTON_TEXT_COLOR => $this->request->getPost(self::BUTTON_TEXT_COLOR),
                self::BACKGROUND_COLOR => $this->request->getPost(self::BACKGROUND_COLOR),
                self::BORDER_COLOR => $this->request->getPost(self::BORDER_COLOR),
                self::FORM_WIDTH => $this->request->getPost(self::FORM_WIDTH),
                self::FORM_MARGIN_BOTTOM => $this->request->getPost(self::FORM_MARGIN_BOTTOM),
            ]
        ]);
        return $resultHttp->setContent($block->toHtml());
    }
}