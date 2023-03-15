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

namespace MageMe\WebForms\Model\Result;


use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractResult extends AbstractModel implements ResultInterface, IdentityInterface
{

    /**
     * Result cache tag
     */
    const CACHE_TAG = 'webforms_result';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_result';

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get store scope
     *
     * @return string
     */
    public function getScope(): string
    {
        return ScopeInterface::SCOPE_STORE;
    }

#region DB getters and setters

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
    }

    /**
     * @inheritDoc
     */
    public function setId($id): ResultInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getFormId(): ?int
    {
        return $this->getData(self::FORM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setFormId(int $formId): ResultInterface
    {
        return $this->setData(self::FORM_ID, $formId);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(?int $storeId): ResultInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    abstract public function getCustomer();

    /**
     * @inheritDoc
     */
    abstract public function getCustomerEmail(): array;

    /**
     * @inheritDoc
     */
    public function setCustomerId(?int $customerId): ResultInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerIp(): string
    {
        return (string)$this->getData(self::CUSTOMER_IP);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerIp(?string $customerIp): ResultInterface
    {
        return $this->setData(self::CUSTOMER_IP, $customerIp);
    }

    /**
     * @inheritDoc
     */
    public function getApproved(): int
    {
        return (int)$this->getData(self::APPROVED);
    }

    /**
     * @inheritDoc
     */
    public function setApproved(int $approved): ResultInterface
    {
        return $this->setData(self::APPROVED, $approved);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): ResultInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): ResultInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getSubmittedFromSerialized(): ?string
    {
        return $this->getData(self::SUBMITTED_FROM_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setSubmittedFromSerialized(?string $submittedFromSerialized): ResultInterface
    {
        return $this->setData(self::SUBMITTED_FROM_SERIALIZED, $submittedFromSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getReferrerPage(): string
    {
        return (string)$this->getData(self::REFERRER_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setReferrerPage(?string $referrerPage): ResultInterface
    {
        return $this->setData(self::REFERRER_PAGE, $referrerPage);
    }
#endregion

    /**
     * @inheritDoc
     */
    public function getSubmittedFrom(): array
    {
        $data = $this->getData(self::SUBMITTED_FROM);
        return is_array($data) ? $data : [];
    }

    /**
     * @inheritDoc
     */
    public function setSubmittedFrom(array $submittedFrom): ResultInterface
    {
        return $this->setData(self::SUBMITTED_FROM, $submittedFrom);
    }

    /**
     * @inheritDoc
     */
    public function setIsReplied(bool $isReplied): ResultInterface
    {
        $this->setData(self::IS_REPLIED, $isReplied);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsReplied(): bool
    {
        return $this->getData(self::IS_REPLIED);
    }

    /**
     * @inheritDoc
     */
    public function setIsRead(bool $isRead): ResultInterface
    {
        $this->setData(self::IS_READ, $isRead);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsRead(): bool
    {
        return $this->getData(self::IS_READ);
    }
}
