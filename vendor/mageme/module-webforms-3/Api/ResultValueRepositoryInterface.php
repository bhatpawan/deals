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


use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Model\ResultValue;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ResultValueRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return ResultValueInterface|ResultValue
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param ResultValueInterface|ResultValue $resultValue
     * @return ResultValueInterface
     * @throws CouldNotSaveException
     */
    public function save(ResultValueInterface $resultValue): ResultValueInterface;

    /**
     * @param ResultValueInterface|ResultValue $resultValue
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ResultValueInterface $resultValue): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ResultValueSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResultValueSearchResultInterface;

    /**
     * @param int $id
     * @return ResultValueSearchResultInterface
     */
    public function getListByResultId(int $id): ResultValueSearchResultInterface;

    /**
     * @param int $id
     * @return ResultValueSearchResultInterface
     */
    public function getListByFieldId(int $id): ResultValueSearchResultInterface;

    /**
     * @param int $resultId
     * @param int $fieldId
     * @return ResultValueInterface|ResultValue
     * @throws NoSuchEntityException
     */
    public function getByResultAndFieldId(int $resultId, int $fieldId);
}