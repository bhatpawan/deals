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


use MageMe\WebForms\Api\Data\TmpFileCustomerNotificationInterface;
use MageMe\WebForms\Model\TmpFileCustomerNotification;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TmpFileCustomerNotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return TmpFileCustomerNotificationInterface|TmpFileCustomerNotification
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return TmpFileCustomerNotificationInterface|TmpFileCustomerNotification
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param TmpFileCustomerNotificationInterface|TmpFileCustomerNotification $file
     * @return TmpFileCustomerNotificationInterface
     * @throws CouldNotSaveException
     */
    public function save(TmpFileCustomerNotificationInterface $file): TmpFileCustomerNotificationInterface;

    /**
     * @param TmpFileCustomerNotificationInterface|TmpFileCustomerNotification $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TmpFileCustomerNotificationInterface $file): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TmpFileCustomerNotificationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileCustomerNotificationSearchResultInterface;

    /**
     * @param int $id
     * @return TmpFileCustomerNotificationSearchResultInterface
     */
    public function getListByFormId(int $id): TmpFileCustomerNotificationSearchResultInterface;

    /**
     * Remove old temp files
     *
     * @return void
     */
    public function cleanup();
}