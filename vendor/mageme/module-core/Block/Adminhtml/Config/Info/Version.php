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


use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends AbstractInfo
{
    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $moduleName = $this->getElementModuleName($element);
        return $this->getVersionHtml($moduleName);
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getVersionHtml(string $moduleName): string
    {
        if (!$moduleName) {
            return '';
        }
        $moduleInfo = $this->_moduleList->getOne($moduleName);
        $version    = (string)$moduleInfo['setup_version'];
        $info       = $this->getModuleInfo($moduleName);
        if (!empty($info) && version_compare($info[self::VERSION], $version, '>')) {
            $version .= ' | ' . sprintf("<a href='%s' target='_blank'>%s</a>",
                    $info[self::RELEASE_NOTES],
                    __("%1 update available", $info[self::VERSION])
                );
        }

        return sprintf('<div class="control-value special">%s</div>', $version);
    }
}
