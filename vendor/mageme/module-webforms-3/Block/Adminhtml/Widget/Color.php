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

namespace MageMe\WebForms\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;

/**
 *
 */
class Color extends Widget
{
    /**
     * @var Factory
     */
    protected $_elementFactory;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        array   $data = []
    )
    {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $value = $element->getData('value');
        if (empty($value)) {
            $value = $this->getData('value');
        }

        $input = $this->_elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setData('value', $value);
        $input->setClass("widget-option input-text admin__control-text");
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $html = $input->getElementHtml();

        $html .= '<script type="text/javascript">
            require(["jquery","colpick"], function ($) {
                $(document).ready(function (e) {
                    var el = $("#' . $input->getHtmlId() . '");
                    var backgroundColor = "' . ($value ?: '#ffffff') . '";
                    el.css("background-color", backgroundColor);
                    el.css("color", $.colpick.contrastColor(backgroundColor));
                    el.colpick({
                        layout:"hex",
                        submit:0,
                        color: backgroundColor,
                        onChange:function(hsb,hex,rgb,el,bySetColor) {
                            $(el).css("background-color", "#" + hex);
                            $(el).css("color", $.colpick.contrastColor(hex));
                            if(!bySetColor) $(el).val("#" + hex);
                    }
                    }).keyup(function(){
                        $(this).colpickSetColor(this.value);
                    });
                });
            });
            </script>';

        $element->unsetData('value');
        $element->setData('after_element_html', $html);

        return $element;
    }

}
