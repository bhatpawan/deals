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

namespace MageMe\WebForms\Model\Repository;


use Exception;
use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Api\StoreSearchResultInterface;
use MageMe\WebForms\Api\StoreSearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\Store as ResourceStore;
use MageMe\WebForms\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use MageMe\WebForms\Model\StoreFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class StoreRepository implements StoreRepositoryInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var ResourceStore
     */
    protected $resource;

    /**
     * @var StoreCollectionFactory
     */
    protected $storeCollectionFactory;

    /**
     * @var StoreSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * StoreRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param ResourceStore $resource
     * @param StoreFactory $storeFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder             $searchCriteriaBuilder,
        StoreSearchResultInterfaceFactory $searchResultInterfaceFactory,
        StoreCollectionFactory            $storeCollectionFactory,
        ResourceStore                     $resource,
        StoreFactory                      $storeFactory,
        CollectionProcessorInterface      $collectionProcessor
    )
    {
        $this->collectionProcessor    = $collectionProcessor;
        $this->storeFactory           = $storeFactory;
        $this->resource               = $resource;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->searchResultFactory    = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function save(StoreInterface $store): StoreInterface
    {
        try {
            $this->resource->save($store);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $store;
    }

    /**
     * @inheritDoc
     */
    public function findEntityStore(int $storeId, string $entityType, int $entityId)
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select()
            ->from($this->resource->getMainTable(), [StoreInterface::ID])
            ->where(StoreInterface::STORE_ID . '=?', $storeId)
            ->where(StoreInterface::ENTITY_TYPE . '=?', $entityType)
            ->where(StoreInterface::ENTITY_ID . '=?', $entityId);

        $id = (int)$connection->fetchOne($select);
        if (!$id) return false;
        return $this->getById($id);
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $store = $this->storeFactory->create();
        $this->resource->load($store, $id);
        if (!$store->getId()) {
            throw new NoSuchEntityException(__('Unable to find store with ID "%1"', $id));
        }
        return $store;
    }

    /**
     * @inheritDoc
     */
    public function deleteAllEntityStoreData(string $entityType, int $entityId): bool
    {
        $stores = $this->getListByEntity($entityType, $entityId)->getItems();
        foreach ($stores as $store) {
            try {
                $this->delete($store);
            } catch (CouldNotDeleteException $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getListByEntity(string $entityType, int $entityId): StoreSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StoreInterface::ENTITY_TYPE, $entityType)
            ->addFilter(StoreInterface::ENTITY_ID, $entityId)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StoreSearchResultInterface
    {
        $collection = $this->storeCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(StoreInterface $store): bool
    {
        try {
            $this->resource->delete($store);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}