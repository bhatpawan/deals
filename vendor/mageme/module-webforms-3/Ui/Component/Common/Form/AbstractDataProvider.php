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


use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

abstract class AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var string
     */
    protected $requestScopeFieldName = 'store';
    /**
     * @var array
     */
    protected $loadedData;
    /**
     * Provider configuration data
     *
     * @var array
     */
    protected $data = [];
    /**
     * @var array
     */
    protected $meta = [];
    /**
     * Data Provider Request Parameter Identifier name
     *
     * @var string
     */
    protected $requestFieldName;
    /**
     * Data Provider Primary Identifier name
     *
     * @var string
     */
    protected $primaryFieldName;
    /**
     * Data Provider name
     *
     * @var string
     */
    protected $name;
    /**
     * @var PoolInterface
     */
    protected $pool;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * AbstractDataProvider constructor.
     * @param RequestInterface $request
     * @param PoolInterface $pool
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        RequestInterface $request,
        PoolInterface    $pool,
        string           $name,
        string           $primaryFieldName,
        string           $requestFieldName,
        array            $meta = [],
        array            $data = []
    )
    {
        $this->data             = $data;
        $this->meta             = $meta;
        $this->requestFieldName = $requestFieldName;
        $this->primaryFieldName = $primaryFieldName;
        $this->name             = $name;
        $this->pool             = $pool;
        $this->request          = $request;
    }

    /**
     * Get current store view scope.
     *
     * @return mixed
     */
    public function getScope()
    {
        return $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->loadedData ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getMeta(): array
    {
        return $this->meta ?? [];
    }

    #region getters

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData()
    {
        return $this->data['config'] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;
    }

    /**
     * @inheritdoc
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return $this->meta[$fieldSetName]['children'][$fieldName] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return $this->meta[$fieldSetName] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return $this->meta[$fieldSetName]['children'] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryFieldName(): string
    {
        return $this->primaryFieldName;
    }

    /**
     * @inheritdoc
     */
    public function getRequestFieldName(): string
    {
        return $this->requestFieldName;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        // No implementation for form
    }

    /**
     * @inheritdoc
     */
    public function addOrder($field, $direction)
    {
        // No implementation for form
    }
    #endregion

    #region useless Collection methods

    /**
     * @inheritdoc
     */
    public function setLimit($offset, $size)
    {
        // No implementation for form
    }

    /**
     * @inheritdoc
     */
    public function getSearchCriteria()
    {
        // No implementation for form
    }

    /**
     * @inheritdoc
     */
    public function getSearchResult()
    {
        // No implementation for form
    }

    /**
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    protected function applyDataModifiers(array $data): array
    {
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @throws LocalizedException
     */
    protected function applyMetaModifiers(array $meta): array
    {
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }
    #endregion
}
