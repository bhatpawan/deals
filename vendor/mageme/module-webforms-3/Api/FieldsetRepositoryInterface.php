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


use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Model\Fieldset;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FieldsetRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @param int|null $storeId
     * @return FieldsetInterface|Fieldset
     * @throws NoSuchEntityException
     */
    public function getById(int $id, ?int $storeId = null);

    /**
     * @param FieldsetInterface|Fieldset $fieldset
     * @return FieldsetInterface
     * @throws CouldNotSaveException
     */
    public function save(FieldsetInterface $fieldset): FieldsetInterface;

    /**
     * @param FieldsetInterface|Fieldset $fieldset
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FieldsetInterface $fieldset): bool;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @param null|int $storeId
     * @return FieldsetSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null, ?int $storeId = null): FieldsetSearchResultInterface;

    /**
     * @param int $id
     * @param int|null $storeId
     * @return FieldsetSearchResultInterface
     */
    public function getListByWebformId(int $id, ?int $storeId = null): FieldsetSearchResultInterface;
}
