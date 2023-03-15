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
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldSearchResultInterface;
use MageMe\WebForms\Api\FieldSearchResultInterfaceFactory;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\ResourceModel\Field as ResourceField;
use MageMe\WebForms\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class FieldRepository implements FieldRepositoryInterface
{

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldCollectionFactory
     */
    protected $fieldCollectionFactory;

    /**
     * @var ResourceField
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var FieldSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * FieldRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FieldFactory $fieldFactory
     * @param FieldCollectionFactory $fieldCollectionFactory
     * @param ResourceField $resource
     * @param FieldSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder             $searchCriteriaBuilder,
        FieldFactory                      $fieldFactory,
        FieldCollectionFactory            $fieldCollectionFactory,
        ResourceField                     $resource,
        FieldSearchResultInterfaceFactory $searchResultInterfaceFactory,
        CollectionProcessorInterface      $collectionProcessor
    )
    {
        $this->fieldFactory           = $fieldFactory;
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->resource               = $resource;
        $this->searchResultFactory    = $searchResultInterfaceFactory;
        $this->collectionProcessor    = $collectionProcessor;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getById(int $id, ?int $storeId = null)
    {
        $type  = $this->resource->getType($id);
        $field = $this->fieldFactory->create($type);
        if (!is_null($storeId)) {
            $field->setStoreId($storeId);
        }
        $this->resource->load($field, $id);
        if (!$field->getId()) {
            throw new NoSuchEntityException(__('Unable to find field with ID "%1"', $id));
        }
        return $field;
    }

    /**
     * @param FieldInterface $field
     * @return FieldInterface
     * @throws CouldNotSaveException
     */
    public function save(FieldInterface $field): FieldInterface
    {
        try {
            $this->resource->save($field);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $field;
    }

    /**
     * @inheritDoc
     */
    public function delete(FieldInterface $field): bool
    {
        try {
            $this->resource->delete($field);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getListByWebformId(int $id, ?int $storeId = null): FieldSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $id)
            ->create();
        return $this->getList($searchCriteria, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria, ?int $storeId = null): FieldSearchResultInterface
    {
        $collection = $this->fieldCollectionFactory->create();
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
    public function getListByFieldsetId(int $id, ?int $storeId = null): FieldSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FIELDSET_ID, $id)
            ->create();
        return $this->getList($searchCriteria, $storeId);
    }
}
