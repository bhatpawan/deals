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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Model\Field\AbstractField;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FieldRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @param int|null $storeId
     * @return FieldInterface|AbstractField
     * @throws NoSuchEntityException
     */
    public function getById(int $id, ?int $storeId = null);

    /**
     * @param FieldInterface|AbstractField $field
     * @return FieldInterface
     * @throws CouldNotSaveException
     */
    public function save(FieldInterface $field): FieldInterface;

    /**
     * @param FieldInterface|AbstractField $field
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FieldInterface $field): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param int|null $storeId
     * @return FieldSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, ?int $storeId = null): FieldSearchResultInterface;

    /**
     * @param int $id
     * @param int|null $storeId
     * @return FieldSearchResultInterface
     */
    public function getListByWebformId(int $id, ?int $storeId = null): FieldSearchResultInterface;

    /**
     * @param int $id
     * @param int|null $storeId
     * @return FieldSearchResultInterface
     */
    public function getListByFieldsetId(int $id, ?int $storeId = null): FieldSearchResultInterface;
}
