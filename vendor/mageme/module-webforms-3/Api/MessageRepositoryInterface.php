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

namespace MageMe\WebForms\Api;


use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Model\Message;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface MessageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return MessageInterface|Message
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param MessageInterface|Message $message
     * @return MessageInterface
     * @throws CouldNotSaveException
     */
    public function save(MessageInterface $message): MessageInterface;

    /**
     * @param MessageInterface|Message $message
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(MessageInterface $message): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return MessageSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): MessageSearchResultInterface;

    /**
     * @param int $id
     * @return MessageSearchResultInterface
     */
    public function getListByResultId(int $id): MessageSearchResultInterface;
}
