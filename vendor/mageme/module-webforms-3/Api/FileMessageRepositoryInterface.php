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


use MageMe\WebForms\Api\Data\FileMessageInterface;
use MageMe\WebForms\Model\FileMessage;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FileMessageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return FileMessageInterface|FileMessage
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return FileMessageInterface|FileMessage
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param FileMessageInterface|FileMessage $file
     * @return FileMessageInterface
     * @throws CouldNotSaveException
     */
    public function save(FileMessageInterface $file): FileMessageInterface;

    /**
     * @param FileMessageInterface|FileMessage $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FileMessageInterface $file): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return FileMessageSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FileMessageSearchResultInterface;

    /**
     * @param int $id
     * @return FileMessageSearchResultInterface
     */
    public function getListByMessageId(int $id): FileMessageSearchResultInterface;

}
