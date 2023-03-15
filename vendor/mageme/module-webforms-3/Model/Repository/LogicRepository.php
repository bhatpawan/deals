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
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Api\LogicSearchResultInterface;
use MageMe\WebForms\Api\LogicSearchResultInterfaceFactory;
use MageMe\WebForms\Model\LogicFactory;
use MageMe\WebForms\Model\ResourceModel\Logic as ResourceLogic;
use MageMe\WebForms\Model\ResourceModel\Logic\CollectionFactory as LogicCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LogicRepository implements LogicRepositoryInterface
{

    /**
     * @var LogicFactory
     */
    protected $logicFactory;

    /**
     * @var LogicCollectionFactory
     */
    protected $logicCollectionFactory;

    /**
     * @var ResourceLogic
     */
    protected $resource;

    /**
     * @var LogicSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * LogicRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LogicFactory $logicFactory
     * @param LogicCollectionFactory $logicCollectionFactory
     * @param ResourceLogic $resource
     * @param LogicSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder             $searchCriteriaBuilder,
        LogicFactory                      $logicFactory,
        LogicCollectionFactory            $logicCollectionFactory,
        ResourceLogic                     $resource,
        LogicSearchResultInterfaceFactory $searchResultInterfaceFactory,
        CollectionProcessorInterface      $collectionProcessor
    )
    {
        $this->logicFactory           = $logicFactory;
        $this->logicCollectionFactory = $logicCollectionFactory;
        $this->resource               = $resource;
        $this->searchResultFactory    = $searchResultInterfaceFactory;
        $this->collectionProcessor    = $collectionProcessor;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id, ?int $storeId = null)
    {
        $logic = $this->logicFactory->create();
        if (!is_null($storeId)) {
            $logic->setStoreId($storeId);
        }
        $this->resource->load($logic, $id);
        if (!$logic->getId()) {
            throw new NoSuchEntityException(__('Unable to find logic with ID "%1"', $id));
        }
        return $logic;
    }

    /**
     * @inheritDoc
     */
    public function save(LogicInterface $logic): LogicInterface
    {
        try {
            $this->resource->save($logic);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $logic;
    }

    /**
     * @inheritDoc
     */
    public function delete(LogicInterface $logic): bool
    {
        try {
            $this->resource->delete($logic);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getListByFieldId(int $id, ?int $storeId = null): LogicSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(LogicInterface::FIELD_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria, ?int $storeId = null): LogicSearchResultInterface
    {
        $collection = $this->logicCollectionFactory->create();

        if (!is_null($storeId)) {
            $collection->setStoreId($storeId);
        }

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
    public function getListByFormId(int $id, bool $all = true, ?int $storeId = null): LogicSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $id);
        if (!$all) {
            $searchCriteria->addFilter(LogicInterface::IS_ACTIVE, true);
        }
        $searchCriteria = $searchCriteria->create();

        return $this->getList($searchCriteria, $storeId);
    }
}
