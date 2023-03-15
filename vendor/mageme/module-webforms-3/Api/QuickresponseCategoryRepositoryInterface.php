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


use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Model\QuickresponseCategory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface QuickresponseCategoryRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return QuickresponseCategoryInterface|QuickresponseCategory
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param QuickresponseCategoryInterface|QuickresponseCategory $quickresponseCategory
     * @return QuickresponseCategoryInterface
     * @throws CouldNotSaveException
     */
    public function save(QuickresponseCategoryInterface $quickresponseCategory): QuickresponseCategoryInterface;

    /**
     * @param QuickresponseCategoryInterface|QuickresponseCategory $quickresponseCategory
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuickresponseCategoryInterface $quickresponseCategory): bool;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return QuickresponseCategorySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): QuickresponseCategorySearchResultInterface;
}