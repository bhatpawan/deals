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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Result;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ResultRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return ResultInterface|Result
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param ResultInterface|Result $result
     * @return ResultInterface
     * @throws CouldNotSaveException
     */
    public function save(ResultInterface $result): ResultInterface;

    /**
     * @param ResultInterface|Result $result
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ResultInterface $result): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ResultSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResultSearchResultInterface;

    /**
     * @param int $id
     * @return ResultSearchResultInterface
     */
    public function getListByFormId(int $id): ResultSearchResultInterface;

    /**
     * @param int $id
     * @return ResultSearchResultInterface
     */
    public function getListByCustomerId(int $id): ResultSearchResultInterface;

    /**
     * API get result data
     *
     * @param int $id
     * @return mixed
     */
    public function getDataById(int $id);
}
