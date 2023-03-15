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
use MageMe\WebForms\Api\Data\FileCustomerNotificationInterface;
use MageMe\WebForms\Api\FileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Api\FileCustomerNotificationSearchResultInterface;
use MageMe\WebForms\Api\FileCustomerNotificationSearchResultInterfaceFactory;
use MageMe\WebForms\Model\FileCustomerNotificationFactory;
use MageMe\WebForms\Model\ResourceModel\FileCustomerNotification as ResourceFileCustomerNotification;
use MageMe\WebForms\Model\ResourceModel\FileCustomerNotification\CollectionFactory as FileCustomerNotificationCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FileCustomerNotificationRepository implements FileCustomerNotificationRepositoryInterface
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
     * @var ResourceFileCustomerNotification
     */
    protected $resource;

    /**
     * @var FileCustomerNotificationSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var FileCustomerNotificationCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var FileCustomerNotificationFactory
     */
    protected $fileFactory;

    /**
     * FileCustomerNotificationRepository constructor.
     * @param FileCustomerNotificationFactory $fileFactory
     * @param FileCustomerNotificationCollectionFactory $fileCollectionFactory
     * @param FileCustomerNotificationSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceFileCustomerNotification $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        FileCustomerNotificationFactory                      $fileFactory,
        FileCustomerNotificationCollectionFactory            $fileCollectionFactory,
        FileCustomerNotificationSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceFileCustomerNotification                     $resource,
        SearchCriteriaBuilder                                $searchCriteriaBuilder,
        CollectionProcessorInterface                         $collectionProcessor
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
     * @param FileCustomerNotificationInterface $file
     * @return FileCustomerNotificationInterface
     * @throws CouldNotSaveException
     */
    public function save(FileCustomerNotificationInterface $file): FileCustomerNotificationInterface
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
    public function delete(FileCustomerNotificationInterface $file): bool
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
        $this->resource->load($file, $hash, FileCustomerNotificationInterface::LINK_HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByFormId(int $id): FileCustomerNotificationSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FileCustomerNotificationInterface::FORM_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): FileCustomerNotificationSearchResultInterface
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