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

namespace MageMe\Core\Plugin\Magento\Config\Model\Config\Structure\Data;


use MageMe\Core\Helper\ModulesHelper;
use Magento\Config\Model\Config\Structure\Data as StructureData;

class ConfigMerge
{
    const MODULE_NAME = 'module_name';

    /**
     * @var ModulesHelper
     */
    private $modulesHelper;

    /**
     * ConfigMerge constructor.
     * @param ModulesHelper $modulesHelper
     */
    public function __construct(
        ModulesHelper $modulesHelper
    )
    {
        $this->modulesHelper = $modulesHelper;
    }

    /**
     * @param StructureData $object
     * @param array $config
     *
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeMerge(StructureData $object, array $config): array
    {
        if (!isset($config['config']['system'])) {
            return [$config];
        }

        /** @var array $sections */
        $sections      = $config['config']['system']['sections'];
        $magemeModules = $this->modulesHelper->getModules();
        foreach ($sections as $sectionId => $section) {
            if (isset($section['tab']) && ($section['tab'] === 'mageme')) {
                if ($sectionId == 'mageme') {
                    $licenseMeta = [];
                    foreach ($magemeModules as $module) {
                        $helper = $this->modulesHelper->getModuleLicenseHelper($module);
                        if (!$helper) continue;
                        $uiHelper = $helper->getUi();
                        $moduleId = $this->modulesHelper->getModuleIdFormName($module);
                        $label    = $helper->getModuleTitle();

                        $options = [
                            'module_id' => $moduleId,
                            'module_name' => $module,
                            'module_label' => $label,
                        ];
                        $uiMeta  = $uiHelper->getMeta($options);
                        if (isset($licenseMeta[$uiMeta['id']])) {
                            $licenseMeta[$uiMeta['id']]['children'] = array_merge($licenseMeta[$uiMeta['id']]['children'], $uiMeta['children']);
                        } else {
                            $licenseMeta[$uiMeta['id']] = $uiMeta;
                        }
                    }
                    $config['config']['system']['sections'][$sectionId]['children']['license']['children'] = $licenseMeta;
                }
            }
        }

        return [$config];
    }
}
