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
use MageMe\WebForms\Api\Data\FileMessageInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\FileMessageSearchResultInterface;
use MageMe\WebForms\Api\FileMessageSearchResultInterfaceFactory;
use MageMe\WebForms\Model\FileMessageFactory;
use MageMe\WebForms\Model\ResourceModel\FileMessage as ResourceFileMessage;
use MageMe\WebForms\Model\ResourceModel\FileMessage\CollectionFactory as FileMessageCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FileMessageRepository implements FileMessageRepositoryInterface
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
     * @var ResourceFileMessage
     */
    protected $resource;

    /**
     * @var FileMessageSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var FileMessageCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var FileMessageFactory
     */
    protected $fileFactory;

    /**
     * FileMessageRepository constructor.
     * @param FileMessageFactory $fileFactory
     * @param FileMessageCollectionFactory $fileCollectionFactory
     * @param FileMessageSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceFileMessage $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        FileMessageFactory                      $fileFactory,
        FileMessageCollectionFactory            $fileCollectionFactory,
        FileMessageSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceFileMessage                     $resource,
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
    public function save(FileMessageInterface $file): FileMessageInterface
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
    public function delete(FileMessageInterface $file): bool
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
        $this->resource->load($file, $hash, FileMessageInterface::LINK_HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByMessageId(int $id): FileMessageSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FileMessageInterface::MESSAGE_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FileMessageSearchResultInterface
    {
        $collection = $this->fileCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
