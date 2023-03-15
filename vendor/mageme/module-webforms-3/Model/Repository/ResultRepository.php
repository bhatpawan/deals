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
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Api\ResultSearchResultInterface;
use MageMe\WebForms\Api\ResultSearchResultInterfaceFactory;
use MageMe\WebForms\Api\ResultValueRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Result as ResourceResult;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory as ResultCollectionFactory;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ResultRepository implements ResultRepositoryInterface
{
    /**
     * @var ResourceResult
     */
    protected $resource;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResultCollectionFactory
     */
    protected $resultCollectionFactory;

    /**
     * @var ResultSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ResultValueRepositoryInterface
     */
    protected $resultValueRepository;

    /**
     * ResultRepository constructor.
     * @param ResultValueRepositoryInterface $resultValueRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResultSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResultCollectionFactory $resultCollectionFactory
     * @param ResourceResult $resource
     * @param ResultFactory $resultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResultValueRepositoryInterface     $resultValueRepository,
        SearchCriteriaBuilder              $searchCriteriaBuilder,
        ResultSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResultCollectionFactory            $resultCollectionFactory,
        ResourceResult                     $resource,
        ResultFactory                      $resultFactory,
        CollectionProcessorInterface       $collectionProcessor
    )
    {
        $this->resource                = $resource;
        $this->resultFactory           = $resultFactory;
        $this->collectionProcessor     = $collectionProcessor;
        $this->resultCollectionFactory = $resultCollectionFactory;
        $this->searchResultFactory     = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->resultValueRepository   = $resultValueRepository;
    }

    /**
     * @inheritDoc
     */
    public function delete(ResultInterface $result): bool
    {
        try {
            $this->resource->delete($result);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(ResultInterface $result): ResultInterface
    {
        try {
            $this->resource->save($result);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getListByFormId(int $id): ResultSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ResultInterface::FORM_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResultSearchResultInterface
    {
        $collection = $this->resultCollectionFactory->create();

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
    public function getListByCustomerId(int $id): ResultSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ResultInterface::CUSTOMER_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getDataById(int $id)
    {
        $result = $this->getById($id);
        $fields = $result->getForm()->getFields();
        $data   = $this->getResultData($result, $fields);
        return [
            'result' => $data
        ];
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $result = $this->resultFactory->create();
        $this->resource->load($result, $id);
        if (!$result->getId()) {
            throw new NoSuchEntityException(__('Unable to find result with ID "%1"', $id));
        }
        return $result;
    }

    /**
     * Get array with result data
     *
     * @param ResultInterface $result
     * @param FieldInterface[] $fields
     * @return array
     */
    public function getResultData(ResultInterface $result, array $fields): array
    {
        $fieldsData = [];
        foreach ($fields as $field) {
            try {
                $value = $this->resultValueRepository->getByResultAndFieldId(
                    $result->getId(),
                    $field->getId()
                )->getValue();
            } catch (Exception $exception) {
                $value = 'Error: ' . $exception->getMessage();
            }

            $fieldsData[] = [
                FieldInterface::ID => $field->getId(),
                FieldInterface::CODE => $field->getCode(),
                'value' => $value,
            ];
        }

        return [
            ResultInterface::ID => $result->getId(),
            ResultInterface::FORM_ID => $result->getFormId(),
            ResultInterface::STORE_ID => $result->getStoreId(),
            ResultInterface::CUSTOMER_ID => $result->getCustomerId(),
            ResultInterface::CUSTOMER_IP => $result->getCustomerIp(),
            ResultInterface::APPROVED => $result->getApproved(),
            ResultInterface::CREATED_AT => $result->getCreatedAt(),
            ResultInterface::UPDATED_AT => $result->getUpdatedAt(),
            ResultInterface::SUBMITTED_FROM_SERIALIZED => $result->getSubmittedFromSerialized(),
            ResultInterface::REFERRER_PAGE => $result->getSubmittedFromSerialized(),
            'fields' => $fieldsData,
        ];
    }
}
