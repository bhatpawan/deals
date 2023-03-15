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

namespace MageMe\WebForms\Api\Data;


use MageMe\WebForms\Model\Form;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\NoSuchEntityException;

interface ResultInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'result_id';
    const FORM_ID = 'form_id';
    const STORE_ID = 'store_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_IP = 'customer_ip';
    const SUBMITTED_FROM_SERIALIZED = 'submitted_from_serialized';
    const REFERRER_PAGE = 'referrer_page';
    const APPROVED = 'approved';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const IS_REPLIED = 'is_replied';
    const IS_READ = 'is_read';
    /**#@-*/

    /**#@+
     * Additional constants for keys of data array.
     */
    const SUBMITTED_FROM = 'submitted_from';
    /**#@-*/

    const MAX_JOIN_FIELDS = 60;


    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set ID
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get webformId
     *
     * @return int|null
     */
    public function getFormId(): ?int;

    /**
     * Set webformId
     *
     * @param int $formId
     * @return $this
     */
    public function setFormId(int $formId): ResultInterface;

    /**
     * Get storeId
     *
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * Set storeId
     *
     * @param int|null $storeId
     * @return $this
     */
    public function setStoreId(?int $storeId): ResultInterface;

    /**
     * Get customerId
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @return array
     */
    public function getCustomerEmail(): array;

    /**
     * @return string
     */
    public function getCustomerName(): string;

    /**
     * Get customer
     *
     * @return Customer|CustomerInterface|bool
     * @throws NoSuchEntityException
     */
    public function getCustomer();

    /**
     * Set customerId
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): ResultInterface;

    /**
     * Get customerIp
     *
     * @return string
     */
    public function getCustomerIp(): string;

    /**
     * Set customerIp
     *
     * @param string|null $customerIp
     * @return $this
     */
    public function setCustomerIp(?string $customerIp): ResultInterface;

    /**
     * Get submitted from serialized
     *
     * @return string|null
     */
    public function getSubmittedFromSerialized(): ?string;

    /**
     * Set submitted from serialized
     *
     * @param string|null $submittedFromSerialized
     * @return $this
     */
    public function setSubmittedFromSerialized(?string $submittedFromSerialized): ResultInterface;

    /**
     * Get referrer page serialized
     *
     * @return string
     */
    public function getReferrerPage(): string;

    /**
     * Set referrer page serialized
     *
     * @param string|null $referrerPage
     * @return $this
     */
    public function setReferrerPage(?string $referrerPage): ResultInterface;

    /**
     * Get approved
     *
     * @return int
     */
    public function getApproved(): int;

    /**
     * Set approved
     *
     * @param int $approved
     * @return $this
     */
    public function setApproved(int $approved): ResultInterface;

    /**
     * Get createdTime
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set createdTime
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): ResultInterface;

    /**
     * Get updateTime
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updateTime
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): ResultInterface;

    /**
     * Get submitted from
     *
     * @return array
     */
    public function getSubmittedFrom(): array;

    /**
     * Set submitted from
     *
     * @param array $submittedFrom
     * @return $this
     */
    public function setSubmittedFrom(array $submittedFrom): ResultInterface;

    /**
     * @return FormInterface|Form
     */
    public function getForm();

    /**
     * @param bool $isReplied
     * @return $this
     */
    public function setIsReplied(bool $isReplied): ResultInterface;

    /**
     * @return bool
     */
    public function getIsReplied(): bool;

    /**
     * @param bool $isRead
     * @return $this
     */
    public function setIsRead(bool $isRead): ResultInterface;

    /**
     * @return bool
     */
    public function getIsRead(): bool;

    /**
     * @return array
     */
    public function getFieldArray(): array;
}
