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

namespace MageMe\Core\Notification\License;


use MageMe\Core\Helper\ModulesHelper;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

class Activate implements MessageInterface
{
    const CORE = 'MageMe_Core';

    /**
     * @var ModulesHelper
     */
    protected $modulesHelper;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Activate constructor.
     * @param UrlInterface $urlBuilder
     * @param ModulesHelper $modulesHelper
     */
    public function __construct(
        UrlInterface  $urlBuilder,
        ModulesHelper $modulesHelper
    )
    {
        $this->modulesHelper = $modulesHelper;
        $this->urlBuilder    = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity()
    {
        return hash("sha256", 'MAGEME_NOT_ACTIVATED');
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed(): bool
    {
        return (bool)count($this->getNotActivatedModules());
    }

    /**
     * @return array
     */
    protected function getNotActivatedModules(): array
    {
        $notActivated = [];
        $modules      = $this->modulesHelper->getModules();
        foreach ($modules as $module) {
            $licenseHelper = $this->modulesHelper->getModuleLicenseHelper($module);
            if (!$licenseHelper) continue;
            if (!$licenseHelper->isActive()) {
                $notActivated[] = $module;
            }
        }
        return $notActivated;
    }

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        $modules = $this->getNotActivatedModules();
        if (empty($modules)) {
            return '';
        }
        $text       = __('Please activate your license for: ');
        $moduleLink = [];
        foreach ($modules as $module) {
            $licenseHelper = $this->modulesHelper->getModuleLicenseHelper($module);
            if (!$licenseHelper) continue;
            if ($licenseHelper->isActive()) {
                continue;
            }
            $label         = $licenseHelper->getModuleTitle();
            $url           = $this->urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'mageme']);
            $moduleLink [] = sprintf('<a href="%s">%s</a>', $url, $label);
        }
        return $text . implode(', ', $moduleLink);
    }

    /**
     * @inheritdoc
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_MAJOR;
    }
}
