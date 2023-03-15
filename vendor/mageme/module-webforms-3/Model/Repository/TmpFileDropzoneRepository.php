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
use MageMe\WebForms\Api\Data\TmpFileDropzoneInterface;
use MageMe\WebForms\Api\TmpFileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\TmpFileDropzoneSearchResultInterface;
use MageMe\WebForms\Api\TmpFileDropzoneSearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\TmpFileDropzone as ResourceTmpFileDropzone;
use MageMe\WebForms\Model\ResourceModel\TmpFileDropzone\CollectionFactory as TmpFileDropzoneCollectionFactory;
use MageMe\WebForms\Model\TmpFileDropzoneFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TmpFileDropzoneRepository implements TmpFileDropzoneRepositoryInterface
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
     * @var ResourceTmpFileDropzone
     */
    protected $resource;

    /**
     * @var TmpFileDropzoneSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var TmpFileDropzoneCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var TmpFileDropzoneFactory
     */
    protected $fileFactory;

    /**
     * TmpFileDropzoneRepository constructor.
     * @param TmpFileDropzoneFactory $fileFactory
     * @param TmpFileDropzoneCollectionFactory $fileCollectionFactory
     * @param TmpFileDropzoneSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceTmpFileDropzone $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TmpFileDropzoneFactory                      $fileFactory,
        TmpFileDropzoneCollectionFactory            $fileCollectionFactory,
        TmpFileDropzoneSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceTmpFileDropzone                     $resource,
        SearchCriteriaBuilder                       $searchCriteriaBuilder,
        CollectionProcessorInterface                $collectionProcessor
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
    public function save(TmpFileDropzoneInterface $file): TmpFileDropzoneInterface
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
        $this->resource->load($file, $hash, TmpFileDropzoneInterface::HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByFieldId(int $id): TmpFileDropzoneSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TmpFileDropzoneInterface::FIELD_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileDropzoneSearchResultInterface
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
            TmpFileDropzoneInterface::CREATED_AT,
            ['lt' => date("Y-m-d H:i:s", strtotime('-1 hour'))]
        );
        foreach ($collection as $file) {
            $this->delete($file);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TmpFileDropzoneInterface $file): bool
    {
        try {
            $this->resource->delete($file);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
