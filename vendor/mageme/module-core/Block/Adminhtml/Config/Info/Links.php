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

namespace MageMe\Core\Block\Adminhtml\Config\Info;


use Exception;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Links extends AbstractInfo
{
    const LINKS_CLASS = 'class';
    const LINKS_STYLE = 'style';
    const LINKS_TITLE = 'title';
    const LINKS_URL = 'url';

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $moduleName = $this->getElementModuleName($element);
        if (!$moduleName) {
            return '';
        }
        $info = $this->getModuleInfo($moduleName);
        if (empty($info)) {
            return '';
        }

        $list = [];
        if (isset($info[self::LINKS]) &&
            is_array($info[self::LINKS])
        ) {
            foreach ($info[self::LINKS] as $link) {
                try {
                    $list[] = sprintf(
                        "<a href='%s' class='%s' style='%s' target='_blank'>%s</a>",
                        $link[self::LINKS_URL],
                        $link[self::LINKS_CLASS],
                        $link[self::LINKS_STYLE],
                        $link[self::LINKS_TITLE]
                    );
                } catch (Exception $exception) {
                    continue;
                }
            }
        }

        return empty($list) ? '' : '<div class="control-value special">' . implode('<br>', $list) . '</div>';
    }
}
