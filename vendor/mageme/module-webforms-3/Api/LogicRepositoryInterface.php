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


use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Model\Logic;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface LogicRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @param int|null $storeId
     * @return LogicInterface|Logic
     * @throws NoSuchEntityException
     */
    public function getById(int $id, ?int $storeId = null);

    /**
     * @param LogicInterface|Logic $logic
     * @return LogicInterface
     * @throws CouldNotSaveException
     */
    public function save(LogicInterface $logic): LogicInterface;

    /**
     * @param LogicInterface|Logic $logic
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(LogicInterface $logic): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param null|int $storeId
     * @return LogicSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, ?int $storeId = null): LogicSearchResultInterface;

    /**
     * @param int $id
     * @param int|null $storeId
     * @return LogicSearchResultInterface
     */
    public function getListByFieldId(int $id, ?int $storeId = null): LogicSearchResultInterface;

    /**
     * @param int $id
     * @param bool $all false - get only active logic
     * @param null|int $storeId
     * @return LogicSearchResultInterface
     */
    public function getListByFormId(int $id, bool $all = true, ?int $storeId = null): LogicSearchResultInterface;

}
