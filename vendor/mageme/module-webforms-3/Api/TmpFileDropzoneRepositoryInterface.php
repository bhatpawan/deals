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


use MageMe\WebForms\Api\Data\TmpFileDropzoneInterface;
use MageMe\WebForms\Model\TmpFileDropzone;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TmpFileDropzoneRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return TmpFileDropzoneInterface|TmpFileDropzone
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return TmpFileDropzoneInterface|TmpFileDropzone
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param TmpFileDropzoneInterface|TmpFileDropzone $file
     * @return TmpFileDropzoneInterface
     * @throws CouldNotSaveException
     */
    public function save(TmpFileDropzoneInterface $file): TmpFileDropzoneInterface;

    /**
     * @param TmpFileDropzoneInterface|TmpFileDropzone $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TmpFileDropzoneInterface $file): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TmpFileDropzoneSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileDropzoneSearchResultInterface;

    /**
     * @param int $id
     * @return TmpFileDropzoneSearchResultInterface
     */
    public function getListByFieldId(int $id): TmpFileDropzoneSearchResultInterface;

    /**
     * Remove old temp files
     *
     * @return void
     */
    public function cleanup();

}
