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

namespace MageMe\WebForms\Model;

use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Api\Utility\StoreDataInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, StoreDataInterface
{
    /**
     * Element cache tag
     */
    const CACHE_TAG = 'webforms_model';

    /**
     * @var string|array|bool
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * AbstractModel constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreFactory $storeFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        StoreFactory             $storeFactory,
        Context                  $context,
        Registry                 $registry,
        AbstractResource         $resource = null,
        AbstractDb               $resourceCollection = null,
        array                    $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->storeFactory    = $storeFactory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param int|null $store_id
     * @return $this
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function setStoreId(?int $store_id): AbstractModel
    {
        $this->getResource()->setStoreId($store_id);

        return $this;
    }

    /**
     * @return int|null
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getStoreId(): ?int
    {
        return $this->getResource()->getStoreId();
    }

    /**
     * @param int $storeId
     * @param mixed $data
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function updateStoreData(int $storeId, $data): AbstractModel
    {
        $store = $this->storeRepository->findEntityStore($storeId, $this->getEntityType(), $this->getId());
        if (!$store) {
            return $this->saveStoreData($storeId, $data);
        }
        $storeData = $store->getStoreData();
        foreach ($data as $key => $val) {
            $storeData[$key] = $val;
        }

        return $this->saveStoreData($storeId, $storeData);
    }

    /**
     * @return string
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getEntityType(): string
    {
        return $this->getResource()->getEntityType();
    }

    /**
     * @param int $storeId
     * @param mixed $data
     * @return $this
     * @throws CouldNotSaveException|LocalizedException
     */
    public function saveStoreData(int $storeId, $data): AbstractModel
    {
        unset($data[$this->getIdFieldName()]);
        $store = $this->storeRepository->findEntityStore($storeId, $this->getEntityType(), $this->getId());
        if (!$store) {
            $store = $this->storeFactory->create();
        }
        $store->setStoreId($storeId)
            ->setEntityType($this->getEntityType())
            ->setEntityId($this->getId())
            ->setStoreData($data);
        $this->storeRepository->save($store);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(): array
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function setStoreData($data): StoreDataInterface
    {
        return $this->setData(self::STORE_DATA, $data);
    }

    /**
     * @inheritDoc
     */
    public function getStoreData()
    {
        return $this->getData(self::STORE_DATA);
    }
}
