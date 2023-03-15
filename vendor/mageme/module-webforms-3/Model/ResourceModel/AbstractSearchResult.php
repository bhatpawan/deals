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
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;
use Zend_Db_Select;

abstract class AbstractSearchResult extends SearchResult
{
    /**
     * @var int|null
     */
    protected $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * AbstractSearchResult constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string|null $resourceModel
     * @param string|null $identifierName
     * @param string|null $connectionName
     * @throws LocalizedException
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        EntityFactory            $entityFactory,
        Logger                   $logger,
        FetchStrategy            $fetchStrategy,
        EventManager             $eventManager,
        string                   $mainTable,
        string                   $resourceModel = null,
        string                   $identifierName = null,
        string                   $connectionName = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel,
            $identifierName, $connectionName);
        $this->storeRepository = $storeRepository;
    }

    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql(): string
    {
        $select      = parent::getSelectCountSql();
        $countSelect = clone $this->getSelect();

        $countSelect->reset(Zend_Db_Select::HAVING);

        return $select;
    }

    /**
     * @return SearchResult
     * @throws LocalizedException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function _afterLoad(): SearchResult
    {
        parent::_afterLoad();
        foreach ($this as $item) {
            $this->_resource->deserializeFieldsFromJSON($item);
        }

        $storeId = $this->getStoreId();
        if (!$storeId) {
            $storeId = $this->getResource()->getStoreId();
        }
        if ($storeId) {
            foreach ($this as $item) {
                $store      = $this->storeRepository->findEntityStore($storeId, $this->getResource()->getEntityType(),
                    $item->getId());
                $useDefault = [];
                foreach ($item->getData() as $key => $value) {
                    $useDefault[$key] = true;
                }
                if ($store && $store->getStoreData()) {
                    foreach ($store->getStoreData() as $key => $val) {
                        $item->setData($key, $val);
                        $useDefault[$key] = false;
                    }
                }

                /** @noinspection SpellCheckingInspection */
                $item->setData('use_default', array_map('intval', $useDefault));
                $item->setData('grid_default', $useDefault);
                $item->setData('store', $storeId);
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
     * @return $this
     */
    public function setStoreId(?int $storeId): AbstractSearchResult
    {
        $this->storeId = $storeId;
        return $this;
    }
}
