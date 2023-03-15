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

namespace MageMe\Core\Helper;


use MageMe\Core\Api\LicenseInterface;
use MageMe\Core\Api\ModuleHelperInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;

class ModulesHelper
{
    const MODULE_PREFIX = 'MageMe_';
    const CORE = 'MageMe_Core';

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * ModulesHelper constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config                 $config,
        ScopeConfigInterface   $scopeConfig,
        ModuleListInterface    $moduleList
    )
    {
        $this->moduleList    = $moduleList;
        $this->scopeConfig   = $scopeConfig;
        $this->config        = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        $modules    = [];
        $moduleList = $this->moduleList->getNames();
        foreach ($moduleList as $name) {
            if (strpos($name, self::MODULE_PREFIX) === false) {
                continue;
            }
            if ($name == self::CORE) {
                continue;
            }
            $modules[] = $name;
        }
        return $modules;
    }

    /**
     * @param string $moduleName
     * @return ModuleHelperInterface|null
     */
    public function getModuleLicenseHelper(string $moduleName): ?ModuleHelperInterface
    {
        return $this->getModuleHelper($moduleName, 'LicenseHelper');
    }

    /**
     * @param string $moduleName
     * @param string $helperName
     * @return ModuleHelperInterface|null
     */
    public function getModuleHelper(string $moduleName, string $helperName): ?ModuleHelperInterface
    {
        $helperClassName = $this->getHelperClassName($moduleName, $helperName);
        if (class_exists($helperClassName)) {
            $helper = $this->objectManager->create($helperClassName);
            if ($helper instanceof ModuleHelperInterface) {
                return $helper;
            }
        }
        return null;
    }

    /**
     * @param string $moduleName
     * @param string $helperName
     * @return string
     */
    protected function getHelperClassName(string $moduleName, string $helperName): string
    {
        return str_replace('_', '\\', $moduleName) . '\Helper\\' . $helperName;
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleIdFormName(string $moduleName): string
    {
        return strtolower(substr($moduleName, strpos($moduleName, '_') + 1));
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleLabelFormName(string $moduleName): string
    {
        return substr($moduleName, strpos($moduleName, '_') + 1);
    }
}
