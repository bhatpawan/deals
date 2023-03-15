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

namespace MageMe\WebForms\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 *
 */
class Color extends Field
{

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array   $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $html  = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery","colpick"], function ($) {
                $(document).ready(function (e) {
                    var el = $("#' . $element->getHtmlId() . '");
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

        return $html;
    }

}
