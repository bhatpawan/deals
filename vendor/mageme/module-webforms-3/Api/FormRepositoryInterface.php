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


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Model\Form;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FormRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @param null|int $storeId
     * @return FormInterface|Form
     * @throws NoSuchEntityException
     */
    public function getById(int $id, ?int $storeId = null);

    /**
     * @param string $code
     * @param null|int $storeId
     * @return FormInterface|Form
     * @throws NoSuchEntityException
     */
    public function getByCode(string $code, ?int $storeId = null);

    /**
     * @param FormInterface|Form $form
     * @return FormInterface
     * @throws CouldNotSaveException
     */
    public function save(FormInterface $form): FormInterface;

    /**
     * @param FormInterface|Form $form
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FormInterface $form): bool;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @param null|int $storeId
     * @return FormSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null, ?int $storeId = null): FormSearchResultInterface;

    /**
     * API get form information
     *
     * @param int $id
     * @return mixed
     */
    public function getDataById(int $id): array;

    /**
     * API get form results
     *
     * @param int $id
     * @param int|null $customerId
     * @return mixed
     */
    public function getResultsById(int $id, ?int $customerId = null): array;

    /**
     * API submit form
     *
     * @param int $id
     * @return mixed
     */
    public function submitById(int $id);
}
