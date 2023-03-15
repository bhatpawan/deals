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
use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use MageMe\WebForms\Api\QuickresponseCategorySearchResultInterface;
use MageMe\WebForms\Api\QuickresponseCategorySearchResultInterfaceFactory;
use MageMe\WebForms\Model\QuickresponseCategoryFactory;
use MageMe\WebForms\Model\ResourceModel\QuickresponseCategory as ResourceQuickresponseCategory;
use MageMe\WebForms\Model\ResourceModel\QuickresponseCategory\CollectionFactory as QuickresponseCategoryCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuickresponseCategoryRepository implements QuickresponseCategoryRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var QuickresponseCategoryFactory
     */
    private $quickresponseCategoryFactory;
    /**
     * @var ResourceQuickresponseCategory
     */
    private $resource;
    /**
     * @var QuickresponseCategoryCollectionFactory
     */
    private $quickresponseCategoryCollectionFactory;
    /**
     * @var QuickresponseCategorySearchResultInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * QuickresponseCategoryRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param QuickresponseCategorySearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param QuickresponseCategoryCollectionFactory $quickresponseCategoryCollectionFactory
     * @param ResourceQuickresponseCategory $resource
     * @param QuickresponseCategoryFactory $quickresponseCategoryFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder                             $searchCriteriaBuilder,
        QuickresponseCategorySearchResultInterfaceFactory $searchResultInterfaceFactory,
        QuickresponseCategoryCollectionFactory            $quickresponseCategoryCollectionFactory,
        ResourceQuickresponseCategory                     $resource,
        QuickresponseCategoryFactory                      $quickresponseCategoryFactory,
        CollectionProcessorInterface                      $collectionProcessor
    )
    {
        $this->collectionProcessor                    = $collectionProcessor;
        $this->quickresponseCategoryFactory           = $quickresponseCategoryFactory;
        $this->resource                               = $resource;
        $this->quickresponseCategoryCollectionFactory = $quickresponseCategoryCollectionFactory;
        $this->searchResultFactory                    = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder                  = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $quickresponseCategory = $this->quickresponseCategoryFactory->create();
        $this->resource->load($quickresponseCategory, $id);
        if (!$quickresponseCategory->getId()) {
            throw new NoSuchEntityException(__('Unable to find quickresponse category with ID "%1"', $id));
        }
        return $quickresponseCategory;
    }

    /**
     * @inheritDoc
     */
    public function save(QuickresponseCategoryInterface $quickresponseCategory): QuickresponseCategoryInterface
    {
        try {
            $this->resource->save($quickresponseCategory);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $quickresponseCategory;
    }

    /**
     * @inheritDoc
     */
    public function delete(QuickresponseCategoryInterface $quickresponseCategory): bool
    {
        try {
            $this->resource->delete($quickresponseCategory);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): QuickresponseCategorySearchResultInterface
    {
        if (is_null($searchCriteria)) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $collection = $this->quickresponseCategoryCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}