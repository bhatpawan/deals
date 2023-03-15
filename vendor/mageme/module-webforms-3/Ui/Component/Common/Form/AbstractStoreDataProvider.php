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

namespace MageMe\WebForms\Ui\Component\Common\Form;


use MageMe\WebForms\Api\Utility\StoreDataInterface;
use MageMe\WebForms\Helper\UIMetaHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

abstract class AbstractStoreDataProvider extends AbstractDataProvider
{
    /**
     * Meta path to field config
     */
    const META_CONFIG = 'arguments/data/config';

    /**
     * Scope labels
     */
    const SCOPE_GLOBAL = '[GLOBAL]';
    const SCOPE_WEBSITE = '[WEBSITE]';
    const SCOPE_STORE = '[STORE]';
    const SCOPE_STORE_VIEW = '[STORE VIEW]';

    const PARAM_STORE = 'store';

    /**
     * @var string
     */
    protected $xmlReferenceName = '';
    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    /**
     * @var DataInterfaceFactory
     */
    protected $uiConfigFactory;
    /**
     * @var UIMetaHelper
     */
    protected $uiMetaHelper;

    /**
     * AbstractStoreDataProvider constructor.
     * @param UIMetaHelper $uiMetaHelper
     * @param DataInterfaceFactory $uiConfigFactory
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     * @param PoolInterface $pool
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        UIMetaHelper         $uiMetaHelper,
        DataInterfaceFactory $uiConfigFactory,
        ArrayManager         $arrayManager,
        RequestInterface     $request,
        PoolInterface        $pool,
        string               $name,
        string               $primaryFieldName,
        string               $requestFieldName,
        array                $meta = [],
        array                $data = []
    )
    {
        parent::__construct($request, $pool, $name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->arrayManager    = $arrayManager;
        $this->uiConfigFactory = $uiConfigFactory;
        $this->uiMetaHelper    = $uiMetaHelper;
    }

    /**
     * Add options for store view functional to fieldset.
     *
     * @param array $meta fieldset
     * @param StoreDataInterface|null $data
     * @return array
     */
    protected function addStoreViewInfo(array $meta, StoreDataInterface $data = null): array
    {
        if (isset($meta['children'])) {
            foreach ($meta['children'] as $key => &$childrenMeta) {
                if (!empty($childrenMeta['children'])) {
                    $childrenMeta = $this->addStoreViewInfo($childrenMeta, $data);
                } else {
                    if (!$this->arrayManager->exists(self::META_CONFIG . '/scopeLabel', $childrenMeta)) {
                        $childrenMeta = $this->addStoreViewLabel($childrenMeta);
                    }
                    if ($this->getScope()) {
                        $fieldScope = $this->arrayManager->get(self::META_CONFIG . '/scopeLabel', $childrenMeta);
                        switch ($fieldScope) {
                            case self::SCOPE_STORE_VIEW:
                            {
                                $childrenMeta = $this->addUseDefaultValueCheckbox($childrenMeta, $data, $key);
                                break;
                            }
                            default:
                            {
                                $childrenMeta = $this->disableField($childrenMeta);
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $meta;
    }

    /**
     * Add store view label.
     *
     * @param array $meta field
     * @return array
     */
    protected function addStoreViewLabel(array $meta): array
    {
        return $this->arrayManager->merge(
            self::META_CONFIG,
            $meta,
            [
                'scopeLabel' => self::SCOPE_STORE_VIEW,
            ]
        );
    }

    /**
     * Add 'Use default value' checkbox
     * and
     * disable field if it is using default value.
     *
     * @param array $meta field
     * @param StoreDataInterface|null $data
     * @param string $fieldName
     * @return array
     */
    protected function addUseDefaultValueCheckbox(array $meta, StoreDataInterface $data = null, string $fieldName = ''): array
    {
        $fieldScope = $this->arrayManager->get(self::META_CONFIG . '/dataScope', $meta, $fieldName);
        return $this->arrayManager->merge(
            self::META_CONFIG,
            $meta,
            [
                'service' => [
                    'template' => 'ui/form/element/helper/service',
                ],
                'disabled' => $this->fieldUsedDefault($data, $fieldScope)
            ]
        );
    }

    /**
     * Check field data for store view
     *
     * @param StoreDataInterface|null $data
     * @param string $fieldScope
     * @return bool
     */
    protected function fieldUsedDefault(StoreDataInterface $data, string $fieldScope): bool
    {
        if ($this->getScope()) {
            $store_data = $data->getStoreData();
            if (is_array($store_data) && array_key_exists($fieldScope, $store_data)) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Disable field
     *
     * @param array $meta
     * @return array
     */
    protected function disableField(array $meta): array
    {
        return $this->arrayManager->merge(
            self::META_CONFIG,
            $meta,
            [
                'disabled' => true
            ]
        );
    }

    /**
     * Check meta for fieldset type
     *
     * @param array $meta
     * @return bool
     */
    protected function isContainer(array $meta): bool
    {
        return !empty($meta['children']);
    }

    /**
     * Get list of fieldsets and fields.
     *
     * @return array
     */
    protected function getFieldsetsMap(): array
    {
        $meta = $this->getXmlMeta($this->xmlReferenceName);
        $meta = $this->uiMetaHelper->disableSanitizeComponentMetadata($meta);
        if (empty($meta['children'])) {
            return [];
        }
        $map = [];
        foreach ($meta['children'] as $key => $node) {
            if (!empty($node['children'])) {
                $map[$key] = $node;
            }
        }
        return $map;
    }

    /**
     * Get meta from xml file
     *
     * @param string $uiComponentName
     * @return array
     */
    protected function getXmlMeta(string $uiComponentName): array
    {
        return $this->uiConfigFactory
                ->create(['componentName' => $uiComponentName])
                ->get($uiComponentName) ?? [];
    }
}
