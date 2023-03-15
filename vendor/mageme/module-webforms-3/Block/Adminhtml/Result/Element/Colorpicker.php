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

namespace MageMe\WebForms\Block\Adminhtml\Result\Element;


use Magento\Framework\Data\Form\Element\AbstractElement;

class Colorpicker extends AbstractElement
{
    /**
     *
     */
    const TYPE = 'colorpicker';

    /**
     * @inheritdoc
     */
    public function getElementHtml(): string
    {
        $this->addClass('input-text admin__control-text');
        $input = parent::getElementHtml();

        return $input . $this->_getScript();
    }

    /**
     * Get colpick script
     *
     * @return string
     */
    private function _getScript(): string
    {
        $value = is_string($this->getData('value')) ? htmlentities((string)$this->getData('value')) : $this->getData('value');
        return '<script type="text/javascript">
            require(["jquery","colpick"], function ($) {
                $(document).ready(function (e) {
                    var el = $("#' . $this->getHtmlId() . '");
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
    }
}
