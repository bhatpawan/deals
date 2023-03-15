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

namespace MageMe\WebForms\Model\ResourceModel;

use MageMe\WebForms\Api\StoreRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * AbstractResource resource model
 *
 */
abstract class AbstractResource extends AbstractDb
{
    const ENTITY_TYPE = '';

    /**
     * @var int|null
     */
    protected $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * AbstractResource constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        Context                  $context,
        ?string                  $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param int $id
     * @return bool
     * @throws LocalizedException
     */
    public function entityExists(int $id): bool
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [$this->getIdFieldName()])
            ->where($this->getIdFieldName() . ' = ?', $id);
        return (bool)$this->getConnection()->fetchOne($select);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        if ($this->getStoreId() && $object->getId()) {
            $store = $this->storeRepository->findEntityStore($this->getStoreId(), $this->getEntityType(),
                $object->getId());
            if (!$store) {
                return $this;
            }

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $object->setStoreData($store->getStoreData());
            if ($store->getStoreData()) {
                foreach ($store->getStoreData() as $key => $value) {
                    $object->setData($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return $this->storeId;
    }

    /**
     * @param int|null $storeId
     */
    public function setStoreId(?int $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getEntityType(): string
    {
        if (!static::ENTITY_TYPE) {
            throw new LocalizedException(
                __('(%1) No type initialized for resource', self::class)
            );
        }
        return static::ENTITY_TYPE;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $this->storeRepository->deleteAllEntityStoreData($this->getEntityType(), $object->getId());
        return parent::_beforeDelete($object);
    }
}
