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


use MageMe\WebForms\Api\Data\FileGalleryInterface;
use MageMe\WebForms\Model\FileGallery;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FileGalleryRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return FileGalleryInterface|FileGallery
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return FileGalleryInterface|FileGallery
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param FileGalleryInterface|FileGallery $file
     * @return FileGalleryInterface
     * @throws CouldNotSaveException
     */
    public function save(FileGalleryInterface $file): FileGalleryInterface;

    /**
     * @param FileGalleryInterface|FileGallery $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FileGalleryInterface $file): bool;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return FileGallerySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): FileGallerySearchResultInterface;

    /**
     * @param int $id
     * @return FileGallerySearchResultInterface
     */
    public function getListByFieldId(int $id): FileGallerySearchResultInterface;

}
