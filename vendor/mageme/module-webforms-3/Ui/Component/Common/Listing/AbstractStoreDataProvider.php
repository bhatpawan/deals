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

namespace MageMe\WebForms\Ui\Component\Common\Listing;


use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Store\Model\Store;

abstract class AbstractStoreDataProvider extends AbstractDataProvider
{
    const PARAM_STORE = 'store';

    /**
     * Columns control name
     *
     * @var string
     */
    protected $columnsName = '';

    /**
     * Store view fields
     *
     * @var array
     */
    protected $storeFields = [];

    /**
     * @inheritdoc
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();
        if (!$this->getScope()) {
            return $meta;
        }
        $meta[$this->columnsName]['arguments']['data']['config']['editorConfig'] = [
            'bulkEnabled' => false,
            'successMsg' => __('You have successfully saved your store view edits.'),
            'templates' => [
                'record' => [
                    'component' => 'MageMe_WebForms/js/grid/editing/store-record',
                    'fieldTmpl' => 'MageMe_WebForms/grid/editing/service-field'
                ]
            ]
        ];
        foreach ($this->storeFields as $field) {
            $meta = $this->addFieldServiceToMeta($meta, $field);
        }
        return $meta;
    }

    /**
     * Get current store view scope.
     *
     * @return mixed
     */
    public function getScope()
    {
        return $this->request->getParam(self::PARAM_STORE, Store::DEFAULT_STORE_ID);
    }

    /**
     * Add field service template to meta
     *
     * @param array $meta
     * @param string $fieldName
     * @return array
     */
    protected function addFieldServiceToMeta(array $meta, string $fieldName): array
    {
        $meta[$this->columnsName]['children'][$fieldName]['arguments']['data']['config']['editor'] = [
            'service' => [
                'template' => 'ui/form/element/helper/service',
            ],
        ];
        return $meta;
    }

    /**
     * @inheritdoc
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $arrItems = parent::searchResultToOutput($searchResult);
        foreach ($arrItems['items'] as &$item) {
            if (isset($item['grid_default'])) {
                foreach ($item['grid_default'] as $key => &$value) {
                    if (!in_array($key, $this->storeFields)) {
                        $value = true;
                    }
                }
            }
        }
        return $arrItems;
    }
}
