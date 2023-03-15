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
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\MessageSearchResultInterface;
use MageMe\WebForms\Api\MessageSearchResultInterfaceFactory;
use MageMe\WebForms\Model\MessageFactory;
use MageMe\WebForms\Model\ResourceModel\Message as ResourceMessage;
use MageMe\WebForms\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class MessageRepository implements MessageRepositoryInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var ResourceMessage
     */
    protected $resource;

    /**
     * @var MessageCollectionFactory
     */
    protected $messageCollectionFactory;

    /**
     * @var MessageSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * MessageRepository constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param MessageSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param MessageCollectionFactory $fileCollectionFactory
     * @param ResourceMessage $resource
     * @param MessageFactory $messageFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder               $searchCriteriaBuilder,
        SortOrderBuilder                    $sortOrderBuilder,
        MessageSearchResultInterfaceFactory $searchResultInterfaceFactory,
        MessageCollectionFactory            $fileCollectionFactory,
        ResourceMessage                     $resource,
        MessageFactory                      $messageFactory,
        CollectionProcessorInterface        $collectionProcessor
    )
    {
        $this->collectionProcessor      = $collectionProcessor;
        $this->messageFactory           = $messageFactory;
        $this->resource                 = $resource;
        $this->messageCollectionFactory = $fileCollectionFactory;
        $this->searchResultFactory      = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->sortOrderBuilder         = $sortOrderBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id)
    {
        $message = $this->messageFactory->create();
        $this->resource->load($message, $id);
        if (!$message->getId()) {
            throw new NoSuchEntityException(__('Unable to find message with ID "%1"', $id));
        }
        return $message;
    }

    /**
     * @inheritDoc
     */
    public function save(MessageInterface $message): MessageInterface
    {
        try {
            $this->resource->save($message);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $message;
    }

    /**
     * @inheritDoc
     */
    public function delete(MessageInterface $message): bool
    {
        try {
            $this->resource->delete($message);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getListByResultId(int $id): MessageSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(MessageInterface::RESULT_ID, $id)
            ->addSortOrder(
                $this->sortOrderBuilder
                    ->setField(MessageInterface::CREATED_AT)
                    ->setDirection('desc')
                    ->create()
            )
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): MessageSearchResultInterface
    {
        $collection = $this->messageCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
