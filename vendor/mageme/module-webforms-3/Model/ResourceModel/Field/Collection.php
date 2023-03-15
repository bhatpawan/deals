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

namespace MageMe\WebForms\Model\ResourceModel\Field;


use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\AbstractSearchResult;
use MageMe\WebForms\Model\ResourceModel\Field as FieldResource;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\Field
 */
class Collection extends AbstractSearchResult
{
    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * Collection constructor.
     * @param FieldFactory $fieldFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string|null $resourceModel
     * @param string|null $identifierName
     * @param string|null $connectionName
     * @throws LocalizedException
     */
    public function __construct(
        FieldFactory             $fieldFactory,
        StoreRepositoryInterface $storeRepository,
        EntityFactoryInterface   $entityFactory,
        LoggerInterface          $logger,
        FetchStrategyInterface   $fetchStrategy,
        ManagerInterface         $eventManager,
        string                   $mainTable,
        string                   $resourceModel = null,
        string                   $identifierName = null,
        string                   $connectionName = null
    )
    {
        parent::__construct($storeRepository, $entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable,
            $resourceModel, $identifierName, $connectionName);
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this|AbstractCollection
     * @throws Exception
     */
    public function loadWithFilter($printQuery = false, $logQuery = false)
    {
        $this->_beforeLoad();
        $this->_renderFilters()->_renderOrders()->_renderLimit();
        $this->printLogQuery($printQuery, $logQuery);
        $data = $this->getData();
        $this->resetData();
        if (is_array($data)) {
            foreach ($data as $row) {
                $item = $this->getFieldModel($row[FieldInterface::TYPE]);
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $item->addData($row);
                $this->beforeAddLoadedItem($item);
                $this->addItem($item);
            }
        }
        $this->_setIsLoaded();
        $this->_afterLoad();
        return $this;
    }

    /**
     * @param string $type
     * @return FieldInterface
     * @throws LocalizedException
     */
    protected function getFieldModel(string $type): FieldInterface
    {
        return $this->fieldFactory->create($type);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(AbstractField::class, FieldResource::class);
    }
}
