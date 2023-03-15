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
use MageMe\WebForms\Api\Data\FileGalleryInterface;
use MageMe\WebForms\Api\FileGalleryRepositoryInterface;
use MageMe\WebForms\Api\FileGallerySearchResultInterface;
use MageMe\WebForms\Api\FileGallerySearchResultInterfaceFactory;
use MageMe\WebForms\Model\FileGalleryFactory;
use MageMe\WebForms\Model\ResourceModel\FileGallery as ResourceFileGallery;
use MageMe\WebForms\Model\ResourceModel\FileGallery\CollectionFactory as FileGalleryCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FileGalleryRepository implements FileGalleryRepositoryInterface
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
     * @var ResourceFileGallery
     */
    protected $resource;

    /**
     * @var FileGallerySearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var FileGalleryCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var FileGalleryFactory
     */
    protected $fileFactory;

    /**
     * FileGalleryRepository constructor.
     * @param FileGalleryFactory $fileFactory
     * @param FileGalleryCollectionFactory $fileCollectionFactory
     * @param FileGallerySearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceFileGallery $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        FileGalleryFactory                      $fileFactory,
        FileGalleryCollectionFactory            $fileCollectionFactory,
        FileGallerySearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceFileGallery                     $resource,
        SearchCriteriaBuilder                   $searchCriteriaBuilder,
        CollectionProcessorInterface            $collectionProcessor
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
    public function save(FileGalleryInterface $file): FileGalleryInterface
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
    public function delete(FileGalleryInterface $file): bool
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
        $this->resource->load($file, $hash, FileGalleryInterface::LINK_HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByFieldId(int $id): FileGallerySearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FileGalleryInterface::FIELD_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): FileGallerySearchResultInterface
    {
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        $collection = $this->fileCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
