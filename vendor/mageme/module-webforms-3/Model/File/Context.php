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

namespace MageMe\WebForms\Model\File;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Image\Factory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;

class Context
{

    /**
     * @var Factory
     */
    protected $imageFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var AbstractResource|null
     */
    protected $resource;
    /**
     * @var AbstractDb|null
     */
    protected $resourceCollection;
    /**
     * @var array
     */
    protected $data;

    /**
     * Context constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Factory $imageFactory
     * @param StoreManager $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface             $scopeConfig,
        Factory                          $imageFactory,
        StoreManager                     $storeManager,
        \Magento\Framework\Model\Context $context,
        Registry                         $registry,
        AbstractResource                 $resource = null,
        AbstractDb                       $resourceCollection = null,
        array                            $data = []
    )
    {
        $this->storeManager       = $storeManager;
        $this->context            = $context;
        $this->registry           = $registry;
        $this->resource           = $resource;
        $this->resourceCollection = $resourceCollection;
        $this->data               = $data;
        $this->imageFactory       = $imageFactory;
        $this->scopeConfig        = $scopeConfig;
    }

    /**
     * @return Factory
     */
    public function getImageFactory(): Factory
    {
        return $this->imageFactory;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    /**
     * @return StoreManager
     */
    public function getStoreManager(): StoreManager
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Framework\Model\Context
     */
    public function getContext(): \Magento\Framework\Model\Context
    {
        return $this->context;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @return AbstractResource|null
     */
    public function getResource(): ?AbstractResource
    {
        return $this->resource;
    }

    /**
     * @return AbstractDb|null
     */
    public function getResourceCollection(): ?AbstractDb
    {
        return $this->resourceCollection;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }


}