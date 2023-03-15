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
use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\FileDropzoneSearchResultInterface;
use MageMe\WebForms\Api\FileDropzoneSearchResultInterfaceFactory;
use MageMe\WebForms\Model\FileDropzoneFactory;
use MageMe\WebForms\Model\ResourceModel\FileDropzone as ResourceFileDropzone;
use MageMe\WebForms\Model\ResourceModel\FileDropzone\CollectionFactory as FileDropzoneCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FileDropzoneRepository implements FileDropzoneRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ResourceFileDropzone
     */
    protected $resource;

    /**
     * @var FileDropzoneSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var FileDropzoneCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var FileDropzoneFactory
     */
    protected $fileFactory;

    /**
     * FileDropzoneRepository constructor.
     * @param FileDropzoneFactory $fileFactory
     * @param FileDropzoneCollectionFactory $fileCollectionFactory
     * @param FileDropzoneSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceFileDropzone $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        FileDropzoneFactory                      $fileFactory,
        FileDropzoneCollectionFactory            $fileCollectionFactory,
        FileDropzoneSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceFileDropzone                     $resource,
        SearchCriteriaBuilder                    $searchCriteriaBuilder,
        CollectionProcessorInterface             $collectionProcessor
    )
    {
        $this->collectionProcessor   = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resource              = $resource;
        $this->searchResultFactory   = $searchResultInterfaceFactory;
        $this->fileCollectionFactory = $fileCollectionFactory;
        $this->fileFactory           = $fileFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $file = $this->fileFactory->create();
        $this->resource->load($file, $id);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with ID "%1"', $id));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function save(FileDropzoneInterface $file): FileDropzoneInterface
    {
        try {
            $this->resource->save($file);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function delete(FileDropzoneInterface $file): bool
    {
        try {
            $this->resource->delete($file);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getByHash(string $hash)
    {
        $file = $this->fileFactory->create();
        $this->resource->load($file, $hash, FileDropzoneInterface::LINK_HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByResultId(int $id): FileDropzoneSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FileDropzoneInterface::RESULT_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FileDropzoneSearchResultInterface
    {
        $collection = $this->fileCollectionFactory->create();

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
    public function getListByFieldId(int $id): FileDropzoneSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FileDropzoneInterface::FIELD_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getListByResultAndFieldId(int $resultId, int $fieldId): FileDropzoneSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FileDropzoneInterface::RESULT_ID, $resultId)
            ->addFilter(FileDropzoneInterface::FIELD_ID, $fieldId)
            ->create();
        return $this->getList($searchCriteria);
    }
}
