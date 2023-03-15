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


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Model\Quickresponse;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface QuickresponseRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return QuickresponseInterface|Quickresponse
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param QuickresponseInterface|Quickresponse $quickresponse
     * @return QuickresponseInterface
     * @throws CouldNotSaveException
     */
    public function save(QuickresponseInterface $quickresponse): QuickresponseInterface;

    /**
     * @param QuickresponseInterface|Quickresponse $quickresponse
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuickresponseInterface $quickresponse): bool;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return QuickresponseSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): QuickresponseSearchResultInterface;
}