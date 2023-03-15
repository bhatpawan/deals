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


use MageMe\WebForms\Api\Data\TmpFileMessageInterface;
use MageMe\WebForms\Model\TmpFileMessage;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TmpFileMessageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return TmpFileMessageInterface|TmpFileMessage
     * @throws NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * @param string $hash
     * @return TmpFileMessageInterface|TmpFileMessage
     * @throws NoSuchEntityException
     */
    public function getByHash(string $hash);

    /**
     * @param TmpFileMessageInterface|TmpFileMessage $file
     * @return TmpFileMessageInterface
     * @throws CouldNotSaveException
     */
    public function save(TmpFileMessageInterface $file): TmpFileMessageInterface;

    /**
     * @param TmpFileMessageInterface|TmpFileMessage $file
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TmpFileMessageInterface $file): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TmpFileMessageSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TmpFileMessageSearchResultInterface;

    /**
     * Remove old temp files
     *
     * @return void
     */
    public function cleanup();
}
