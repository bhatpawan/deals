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

namespace MageMe\Core\Ui;


use MageMe\Core\Api\Ui\LicenseInterface;
use MageMe\Core\Block\Adminhtml\Config\Info\Links;
use MageMe\Core\Block\Adminhtml\Config\Info\Version;
use Magento\Framework\Module\ModuleListInterface;

class License implements LicenseInterface
{
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var string
     */
    protected $groupId;

    /**
     * @var string
     */
    protected $groupLabel;

    public function __construct(
        ModuleListInterface $moduleList,
        string              $groupId = '',
        string              $groupLabel = ''
    )
    {
        $this->moduleList = $moduleList;
        $this->groupId    = $groupId;
        $this->groupLabel = $groupLabel;
    }

    public function getMeta(array $options = []): array
    {
        return $this->getDefaultMeta(
            $options['module_id'],
            $options['module_name'],
            $options['module_label']
        );
    }

    /**
     * @param string $moduleId
     * @param string $moduleName
     * @param string $moduleLabel
     * @return array
     */
    protected function getDefaultMeta(
        string $moduleId,
        string $moduleName,
        string $moduleLabel): array
    {
        $meta       = array_merge($this->getDefaultGroupMeta($moduleId),
            [
                'id' => $moduleId . '_license',
                'translate' => 'label comment',
                'label' => $moduleLabel,
                'comment' => '<div id="' . $moduleId . '_license_messages" class="messages"></div>'
            ]
        );
        $path       = $moduleId . '/license';

        $versionMeta = array_merge($this->getDefaultFieldMeta($path, $moduleId, $moduleName),
            [
                'id' => 'version',
                'sortOrder' => '1',
                'label' => 'Version',
                'frontend_model' => Version::class,
            ]
        );

        $serialMeta = array_merge($this->getDefaultFieldMeta($path, $moduleId, $moduleName),
            [
                'id' => 'serial',
                'translate' => 'label comment',
                'sortOrder' => '10',
                'label' => 'Serial #',
                'comment' => 'Enter your license serial number here',
                'frontend_model' => \MageMe\Core\Block\Adminhtml\Config\License::class
            ]
        );

        $linksMeta  = array_merge($this->getDefaultFieldMeta($path, $moduleId, $moduleName),
            [
                'id' => 'links',
                'sortOrder' => '20',
                'label' => 'Useful Links',
                'frontend_model' => Links::class,
            ]
        );

        $meta['children'] = [
            $versionMeta,
            $serialMeta,
            $linksMeta,
        ];

        if ($this->isGroup()) {
            return $this->getGroupMeta($meta);
        }
        return $meta;
    }

    /**
     * @param string $path
     * @return array
     */
    protected function getDefaultGroupMeta(string $path): array
    {
        return [
            'type' => 'text',
            'translate' => 'label',
            'showInDefault' => '1',
            'showInWebsite' => '0',
            'showInStore' => '0',
            'sortOrder' => '1',
            'children' => [],
            '_elementType' => 'group',
            'path' => $path,
        ];
    }

    /**
     * @param string $path
     * @param string $moduleId
     * @param string $moduleName
     * @return array
     */
    protected function getDefaultFieldMeta(string $path, string $moduleId, string $moduleName): array
    {
        return [
            'type' => 'text',
            'translate' => 'label',
            'showInDefault' => '1',
            'showInWebsite' => '0',
            'showInStore' => '0',
            'sortOrder' => '1',
            '_elementType' => 'field',
            'path' => $path,
            'module_id' => $moduleId,
            'module_name' => $moduleName
        ];
    }

    /**
     * @return bool
     */
    protected function isGroup(): bool
    {
        return (bool)$this->getGroupId();
    }

    /**
     * @return string
     */
    protected function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param array $children
     * @return array
     */
    protected function getGroupMeta(array $children): array
    {
        $id    = $this->getGroupId();
        $label = $this->getGroupLabel() ?: $id;
        return array_merge($this->getDefaultGroupMeta($id),
            [
                'id' => $id,
                'translate' => 'label',
                'showInWebsite' => '0',
                'showInStore' => '0',
                'sortOrder' => '2',
                'label' => $label,
                'children' => [$children]
            ]
        );
    }

    /**
     * @return string
     */
    protected function getGroupLabel()
    {
        return $this->groupLabel;
    }
}