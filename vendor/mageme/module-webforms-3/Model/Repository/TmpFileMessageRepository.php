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
use MageMe\WebForms\Api\Data\TmpFileMessageInterface;
use MageMe\WebForms\Api\TmpFileMessageRepositoryInterface;
use MageMe\WebForms\Api\TmpFileMessageSearchResultInterface;
use MageMe\WebForms\Api\TmpFileMessageSearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\TmpFileMessage as ResourceTmpFileMessage;
use MageMe\WebForms\Model\ResourceModel\TmpFileMessage\CollectionFactory as TmpFileMessageCollectionFactory;
use MageMe\WebForms\Model\TmpFileMessageFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TmpFileMessageRepository implements TmpFileMessageRepositoryInterface
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
     * @var ResourceTmpFileMessage
     */
    protected $resource;

    /**
     * @var TmpFileMessageSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var TmpFileMessageCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var TmpFileMessageFactory
     */
    protected $fileFactory;

    /**
     * TmpFileMessageRepository constructor.
     * @param TmpFileMessageFactory $fileFactory
     * @param TmpFileMessageCollectionFactory $fileCollectionFactory
     * @param TmpFileMessageSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceTmpFileMessage $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TmpFileMessageFactory                      $fileFactory,
        TmpFileMessageCollectionFactory            $fileCollectionFactory,
        TmpFileMessageSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceTmpFileMessage                     $resource,
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
    public function save(TmpFileMessageInterface $file): TmpFileMessageInterface
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
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileMessageSearchResultInterface
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
    public function getByHash(string $hash)
    {
        $file = $this->fileFactory->create();
        $this->resource->load($file, $hash, TmpFileMessageInterface::HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     * @throws CouldNotDeleteException
     */
    public function cleanup()
    {

        $collection = $this->fileCollectionFactory->create();
        $collection->addFieldToFilter(
            TmpFileMessageInterface::CREATED_AT,
            ['lt' => date("Y-m-d H:i:s", strtotime('-1 hour'))]
        );
        foreach ($collection as $file) {
            $this->delete($file);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TmpFileMessageInterface $file): bool
    {
        try {
            $this->resource->delete($file);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
