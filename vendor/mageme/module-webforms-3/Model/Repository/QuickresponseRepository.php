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
use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use MageMe\WebForms\Api\QuickresponseSearchResultInterface;
use MageMe\WebForms\Api\QuickresponseSearchResultInterfaceFactory;
use MageMe\WebForms\Model\QuickresponseFactory;
use MageMe\WebForms\Model\ResourceModel\Quickresponse as ResourceQuickresponse;
use MageMe\WebForms\Model\ResourceModel\Quickresponse\CollectionFactory as QuickresponseCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuickresponseRepository implements QuickresponseRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var QuickresponseFactory
     */
    protected $quickresponseFactory;

    /**
     * @var ResourceQuickresponse
     */
    protected $resource;

    /**
     * @var QuickresponseCollectionFactory
     */
    protected $quickresponseCollectionFactory;

    /**
     * @var QuickresponseSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * QuickresponseRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param QuickresponseSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param QuickresponseCollectionFactory $quickresponseCollectionFactory
     * @param ResourceQuickresponse $resource
     * @param QuickresponseFactory $quickresponseFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder                     $searchCriteriaBuilder,
        QuickresponseSearchResultInterfaceFactory $searchResultInterfaceFactory,
        QuickresponseCollectionFactory            $quickresponseCollectionFactory,
        ResourceQuickresponse                     $resource,
        QuickresponseFactory                      $quickresponseFactory,
        CollectionProcessorInterface              $collectionProcessor
    )
    {
        $this->collectionProcessor            = $collectionProcessor;
        $this->quickresponseFactory           = $quickresponseFactory;
        $this->resource                       = $resource;
        $this->quickresponseCollectionFactory = $quickresponseCollectionFactory;
        $this->searchResultFactory            = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder          = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $quickresponse = $this->quickresponseFactory->create();
        $this->resource->load($quickresponse, $id);
        if (!$quickresponse->getId()) {
            throw new NoSuchEntityException(__('Unable to find quickresponse with ID "%1"', $id));
        }
        return $quickresponse;
    }

    /**
     * @inheritDoc
     */
    public function save(QuickresponseInterface $quickresponse): QuickresponseInterface
    {
        try {
            $this->resource->save($quickresponse);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $quickresponse;
    }

    /**
     * @inheritDoc
     */
    public function delete(QuickresponseInterface $quickresponse): bool
    {
        try {
            $this->resource->delete($quickresponse);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): QuickresponseSearchResultInterface
    {
        if (is_null($searchCriteria)) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $collection = $this->quickresponseCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

}