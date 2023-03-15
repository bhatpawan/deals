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


use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Model\Store;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface StoreRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return StoreInterface|Store
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param StoreInterface|Store $store
     * @return StoreInterface
     * @throws CouldNotSaveException
     */
    public function save(StoreInterface $store): StoreInterface;

    /**
     * @param StoreInterface|Store $store
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(StoreInterface $store): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StoreSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StoreSearchResultInterface;

    /**
     * @param string $entityType
     * @param int $entityId
     * @return StoreSearchResultInterface
     */
    public function getListByEntity(string $entityType, int $entityId): StoreSearchResultInterface;

    /**
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @return StoreInterface|Store|bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function findEntityStore(int $storeId, string $entityType, int $entityId);

    /**
     * @param string $entityType
     * @param int $entityId
     * @return bool
     */
    public function deleteAllEntityStoreData(string $entityType, int $entityId): bool;

}