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


use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Model\FileDropzone;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FileDropzoneRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return FileDropzoneInterface|FileDropzone
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return FileDropzoneInterface|FileDropzone
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param FileDropzoneInterface|FileDropzone $file
     * @return FileDropzoneInterface
     * @throws CouldNotSaveException
     */
    public function save(FileDropzoneInterface $file): FileDropzoneInterface;

    /**
     * @param FileDropzoneInterface|FileDropzone $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FileDropzoneInterface $file): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return FileDropzoneSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FileDropzoneSearchResultInterface;

    /**
     * @param int $id
     * @return FileDropzoneSearchResultInterface
     */
    public function getListByResultId(int $id): FileDropzoneSearchResultInterface;

    /**
     * @param int $id
     * @return FileDropzoneSearchResultInterface
     */
    public function getListByFieldId(int $id): FileDropzoneSearchResultInterface;

    /**
     * @param int $resultId
     * @param int $fieldId
     * @return FileDropzoneSearchResultInterface
     */
    public function getListByResultAndFieldId(int $resultId, int $fieldId): FileDropzoneSearchResultInterface;
}
