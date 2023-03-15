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
use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Api\ResultValueRepositoryInterface;
use MageMe\WebForms\Api\ResultValueSearchResultInterface;
use MageMe\WebForms\Api\ResultValueSearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\ResultValue as ResourceResultValue;
use MageMe\WebForms\Model\ResourceModel\ResultValue\CollectionFactory as ResultValueCollectionFactory;
use MageMe\WebForms\Model\ResultValueFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ResultValueRepository
 * @package MageMe\WebForms\Model
 */
class ResultValueRepository implements ResultValueRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResultValueFactory
     */
    protected $resultValueFactory;

    /**
     * @var ResourceResultValue
     */
    protected $resource;

    /**
     * @var ResultValueCollectionFactory
     */
    protected $resultValueCollectionFactory;

    /**
     * @var ResultValueSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * ResultValueRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResultValueSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResultValueCollectionFactory $resultValueCollectionFactory
     * @param ResourceResultValue $resource
     * @param ResultValueFactory $resultValueFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder                   $searchCriteriaBuilder,
        ResultValueSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResultValueCollectionFactory            $resultValueCollectionFactory,
        ResourceResultValue                     $resource,
        ResultValueFactory                      $resultValueFactory,
        CollectionProcessorInterface            $collectionProcessor
    )
    {
        $this->collectionProcessor          = $collectionProcessor;
        $this->resultValueFactory           = $resultValueFactory;
        $this->resource                     = $resource;
        $this->resultValueCollectionFactory = $resultValueCollectionFactory;
        $this->searchResultFactory          = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder        = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $resultValue = $this->resultValueFactory->create();
        $this->resource->load($resultValue, $id);
        if (!$resultValue->getId()) {
            throw new NoSuchEntityException(__('Unable to find value with ID "%1"', $id));
        }
        return $resultValue;
    }

    /**
     * @inheritDoc
     */
    public function save(ResultValueInterface $resultValue): ResultValueInterface
    {
        try {
            $this->resource->save($resultValue);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $resultValue;
    }

    /**
     * @inheritDoc
     */
    public function delete(ResultValueInterface $resultValue): bool
    {
        try {
            $this->resource->delete($resultValue);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getListByResultId(int $id): ResultValueSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ResultValueInterface::RESULT_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResultValueSearchResultInterface
    {
        $collection = $this->resultValueCollectionFactory->create();

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
    public function getListByFieldId(int $id): ResultValueSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ResultValueInterface::FIELD_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getByResultAndFieldId(int $resultId, int $fieldId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ResultValueInterface::RESULT_ID, $resultId)
            ->addFilter(ResultValueInterface::FIELD_ID, $fieldId)
            ->create();
        $searchResults  = $this->getList($searchCriteria);
        if ($searchResults->getTotalCount() < 1) {
            throw new NoSuchEntityException(__('Unable to find value with Result ID "%1" and Field ID "%2"', [$resultId, $fieldId]));
        }
        $value = '';
        foreach ($searchResults->getItems() as $resultValue) {
            $value = $resultValue;
            break;
        }
        return $value;
    }
}