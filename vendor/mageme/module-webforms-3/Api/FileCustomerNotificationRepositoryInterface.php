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


use MageMe\WebForms\Api\Data\FileCustomerNotificationInterface;
use MageMe\WebForms\Model\FileCustomerNotification;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FileCustomerNotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return FileCustomerNotificationInterface|FileCustomerNotification
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return FileCustomerNotificationInterface|FileCustomerNotification
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param FileCustomerNotificationInterface|FileCustomerNotification $file
     * @return FileCustomerNotificationInterface
     * @throws CouldNotSaveException
     */
    public function save(FileCustomerNotificationInterface $file): FileCustomerNotificationInterface;

    /**
     * @param FileCustomerNotificationInterface|FileCustomerNotification $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FileCustomerNotificationInterface $file): bool;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return FileCustomerNotificationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): FileCustomerNotificationSearchResultInterface;

    /**
     * @param int $id
     * @return FileCustomerNotificationSearchResultInterface
     */
    public function getListByFormId(int $id): FileCustomerNotificationSearchResultInterface;
}