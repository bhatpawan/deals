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
use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;
use MageMe\WebForms\Api\TmpFileGalleryRepositoryInterface;
use MageMe\WebForms\Api\TmpFileGallerySearchResultInterface;
use MageMe\WebForms\Api\TmpFileGallerySearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\TmpFileGallery as ResourceTmpFileGallery;
use MageMe\WebForms\Model\ResourceModel\TmpFileGallery\CollectionFactory as TmpFileGalleryCollectionFactory;
use MageMe\WebForms\Model\TmpFileGalleryFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TmpFileGalleryRepository implements TmpFileGalleryRepositoryInterface
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
     * @var ResourceTmpFileGallery
     */
    protected $resource;

    /**
     * @var TmpFileGallerySearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var TmpFileGalleryCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var TmpFileGalleryFactory
     */
    protected $fileFactory;

    /**
     * TmpFileGalleryRepository constructor.
     * @param TmpFileGalleryFactory $fileFactory
     * @param TmpFileGalleryCollectionFactory $fileCollectionFactory
     * @param TmpFileGallerySearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceTmpFileGallery $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TmpFileGalleryFactory                      $fileFactory,
        TmpFileGalleryCollectionFactory            $fileCollectionFactory,
        TmpFileGallerySearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceTmpFileGallery                     $resource,
        SearchCriteriaBuilder                      $searchCriteriaBuilder,
        CollectionProcessorInterface               $collectionProcessor
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
    public function save(TmpFileGalleryInterface $file): TmpFileGalleryInterface
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
    public function getByHash(string $hash)
    {
        $file = $this->fileFactory->create();
        $this->resource->load($file, $hash, TmpFileGalleryInterface::HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByFieldId(int $id): TmpFileGallerySearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TmpFileGalleryInterface::FIELD_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileGallerySearchResultInterface
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
     * @throws CouldNotDeleteException
     */
    public function cleanup()
    {

        $collection = $this->fileCollectionFactory->create();
        $collection->addFieldToFilter(
            TmpFileGalleryInterface::CREATED_AT,
            ['lt' => date("Y-m-d H:i:s", strtotime('-1 hour'))]
        );
        foreach ($collection as $file) {
            $this->delete($file);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TmpFileGalleryInterface $file): bool
    {
        try {
            $this->resource->delete($file);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
