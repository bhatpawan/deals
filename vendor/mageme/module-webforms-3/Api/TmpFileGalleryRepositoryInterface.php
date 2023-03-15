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


use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;
use MageMe\WebForms\Model\TmpFileGallery;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TmpFileGalleryRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return TmpFileGalleryInterface|TmpFileGallery
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return TmpFileGalleryInterface|TmpFileGallery
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param TmpFileGalleryInterface|TmpFileGallery $file
     * @return TmpFileGalleryInterface
     * @throws CouldNotSaveException
     */
    public function save(TmpFileGalleryInterface $file): TmpFileGalleryInterface;

    /**
     * @param TmpFileGalleryInterface|TmpFileGallery $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TmpFileGalleryInterface $file): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TmpFileGallerySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileGallerySearchResultInterface;

    /**
     * @param int $id
     * @return TmpFileGallerySearchResultInterface
     */
    public function getListByFieldId(int $id): TmpFileGallerySearchResultInterface;

    /**
     * Remove old temp files
     *
     * @return void
     */
    public function cleanup();

}
