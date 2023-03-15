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
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FieldsetSearchResultInterface;
use MageMe\WebForms\Api\FieldsetSearchResultInterfaceFactory;
use MageMe\WebForms\Model\FieldsetFactory;
use MageMe\WebForms\Model\ResourceModel\Fieldset as ResourceFieldset;
use MageMe\WebForms\Model\ResourceModel\Fieldset\CollectionFactory as FieldsetCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FieldsetRepository implements FieldsetRepositoryInterface
{
    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var FieldsetCollectionFactory
     */
    protected $fieldsetCollectionFactory;

    /**
     * @var ResourceFieldset
     */
    protected $resource;

    /**
     * @var FieldsetSearchResultInterfaceFactory
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
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * FieldsetRepository constructor.
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FieldsetFactory $fieldsetFactory
     * @param FieldsetCollectionFactory $fieldsetCollectionFactory
     * @param ResourceFieldset $resource
     * @param FieldsetSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SortOrderBuilder                     $sortOrderBuilder,
        SearchCriteriaBuilder                $searchCriteriaBuilder,
        FieldsetFactory                      $fieldsetFactory,
        FieldsetCollectionFactory            $fieldsetCollectionFactory,
        ResourceFieldset                     $resource,
        FieldsetSearchResultInterfaceFactory $searchResultInterfaceFactory,
        CollectionProcessorInterface         $collectionProcessor
    )
    {
        $this->fieldsetFactory           = $fieldsetFactory;
        $this->fieldsetCollectionFactory = $fieldsetCollectionFactory;
        $this->resource                  = $resource;
        $this->searchResultFactory       = $searchResultInterfaceFactory;
        $this->collectionProcessor       = $collectionProcessor;
        $this->searchCriteriaBuilder     = $searchCriteriaBuilder;
        $this->sortOrderBuilder          = $sortOrderBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id, ?int $storeId = null)
    {
        $fieldset = $this->fieldsetFactory->create();
        if (!is_null($storeId)) {
            $fieldset->setStoreId($storeId);
        }
        $this->resource->load($fieldset, $id);
        if (!$fieldset->getId()) {
            throw new NoSuchEntityException(__('Unable to find fieldset with ID "%1"', $id));
        }
        return $fieldset;
    }

    /**
     * @inheritDoc
     */
    public function save(FieldsetInterface $fieldset): FieldsetInterface
    {
        try {
            $this->resource->save($fieldset);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $fieldset;
    }

    /**
     * @inheritDoc
     */
    public function delete(FieldsetInterface $fieldset): bool
    {
        try {
            $this->resource->delete($fieldset);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getListByWebformId(int $id, ?int $storeId = null): FieldsetSearchResultInterface
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(FieldsetInterface::POSITION)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldsetInterface::FORM_ID, $id)
            ->addSortOrder($sortOrder)
            ->create();
        return $this->getList($searchCriteria, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null, ?int $storeId = null): FieldsetSearchResultInterface
    {
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        $collection = $this->fieldsetCollectionFactory->create();
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
}
