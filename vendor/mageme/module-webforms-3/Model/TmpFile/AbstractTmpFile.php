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

namespace MageMe\WebForms\Model\TmpFile;


use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;

abstract class AbstractTmpFile extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'webforms_tmp_file';

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * AbstractFile constructor.
     * @param StoreManager $storeManager
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        StoreManager     $storeManager,
        Context          $context,
        Registry         $registry,
        AbstractResource $resource = null,
        AbstractDb       $resourceCollection = null,
        array            $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(): array
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get full path to file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFullPath(): string
    {
        $store = $this->getStore();
        return $store ? $store->getBaseMediaDir() . '/' . $this->getPath() : '';
    }

    /**
     * Get store
     *
     * @param int|null $storeId
     * @return bool|int|StoreInterface|string
     */
    public function getStore(?int $storeId = null)
    {
        try{
            return $this->storeManager->getStore($storeId);
        }
        catch(NoSuchEntityException $e){
            return false;
        }
    }

}
