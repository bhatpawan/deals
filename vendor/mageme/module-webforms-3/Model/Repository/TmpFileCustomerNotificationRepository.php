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
use MageMe\WebForms\Api\Data\TmpFileCustomerNotificationInterface;
use MageMe\WebForms\Api\TmpFileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Api\TmpFileCustomerNotificationSearchResultInterface;
use MageMe\WebForms\Api\TmpFileCustomerNotificationSearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\TmpFileCustomerNotification as ResourceTmpFileCustomerNotification;
use MageMe\WebForms\Model\ResourceModel\TmpFileCustomerNotification\CollectionFactory as TmpFileCustomerNotificationCollectionFactory;
use MageMe\WebForms\Model\TmpFileCustomerNotificationFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TmpFileCustomerNotificationRepository implements TmpFileCustomerNotificationRepositoryInterface
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
     * @var ResourceTmpFileCustomerNotification
     */
    protected $resource;

    /**
     * @var TmpFileCustomerNotificationSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var TmpFileCustomerNotificationCollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var TmpFileCustomerNotificationFactory
     */
    protected $fileFactory;

    /**
     * TmpFileCustomerNotificationRepository constructor.
     * @param TmpFileCustomerNotificationFactory $fileFactory
     * @param TmpFileCustomerNotificationCollectionFactory $fileCollectionFactory
     * @param TmpFileCustomerNotificationSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param ResourceTmpFileCustomerNotification $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TmpFileCustomerNotificationFactory                      $fileFactory,
        TmpFileCustomerNotificationCollectionFactory            $fileCollectionFactory,
        TmpFileCustomerNotificationSearchResultInterfaceFactory $searchResultInterfaceFactory,
        ResourceTmpFileCustomerNotification                     $resource,
        SearchCriteriaBuilder                                   $searchCriteriaBuilder,
        CollectionProcessorInterface                            $collectionProcessor
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
    public function save(TmpFileCustomerNotificationInterface $file): TmpFileCustomerNotificationInterface
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
        $this->resource->load($file, $hash, TmpFileCustomerNotificationInterface::HASH);
        if (!$file->getId()) {
            throw new NoSuchEntityException(__('Unable to find file with hash "%1"', $hash));
        }
        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getListByFormId(int $id): TmpFileCustomerNotificationSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TmpFileCustomerNotificationInterface::FORM_ID, $id)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileCustomerNotificationSearchResultInterface
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
            TmpFileCustomerNotificationInterface::CREATED_AT,
            ['lt' => date("Y-m-d H:i:s", strtotime('-1 hour'))]
        );
        foreach ($collection as $file) {
            $this->delete($file);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(TmpFileCustomerNotificationInterface $file): bool
    {
        try {
            $this->resource->delete($file);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

}