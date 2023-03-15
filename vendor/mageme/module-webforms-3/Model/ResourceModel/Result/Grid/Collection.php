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

namespace MageMe\WebForms\Model\ResourceModel\Result\Grid;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Field as FieldResource;
use MageMe\WebForms\Model\ResourceModel\Result\Collection as ResultCollection;
use MageMe\WebForms\Model\ResourceModel\ResultValue as ResultValueResource;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

/**
 * Collection for displaying grid of cms blocks.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends ResultCollection implements SearchResultInterface
{
    /**
     * @var string class name of document
     */
    protected $document = Document::class;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * Collection constructor.
     * @param RequestInterface $request
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param CustomerRepositoryInterface $customerRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        RequestInterface            $request,
        string                      $mainTable,
        string                      $eventPrefix,
        string                      $eventObject,
        string                      $resourceModel,
        CustomerRepositoryInterface $customerRepository,
        FieldRepositoryInterface    $fieldRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        EntityFactoryInterface      $entityFactory,
        LoggerInterface             $logger,
        FetchStrategyInterface      $fetchStrategy,
        ManagerInterface            $eventManager,
        AdapterInterface            $connection = null,
        AbstractDb                  $resource = null
    )
    {
        $this->request = $request;
        parent::__construct($customerRepository, $fieldRepository, $searchCriteriaBuilder, $entityFactory, $logger,
            $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($this->document, $resourceModel);
        $this->setMainTable($mainTable);
        $this->loadValues = true;
    }

    /**
     * @inheritDoc
     */
    public function getAggregations(): AggregationInterface
    {
        return $this->aggregations;
    }

    /**
     * @inheritDoc
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @inheritDoc
     */
    public function getSearchCriteria()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTotalCount(): int
    {
        return $this->getSize();
    }

    /**
     * @inheritDoc
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param ExtensibleDataInterface[]|array $items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null): Collection
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $formId = (int)$this->request->getParam(ResultInterface::FORM_ID);
        if ($formId) {
            $this->addValuesToResult($formId);
        }
        return $this;
    }

    /**
     * @param int $formId
     * @return void
     */
    public function addValuesToResult(int $formId)
    {
        $fields = $this->getFieldsIds($formId);
        if (count($fields) < ResultInterface::MAX_JOIN_FIELDS) {
            foreach ($fields as $fieldId) {
                $resultValues = $this->getResource()->getConnection()->select()
                    ->from($this->getTable(ResultValueResource::DB_TABLE),
                        ['_'.ResultValueInterface::RESULT_ID => ResultValueInterface::RESULT_ID, ResultValueInterface::VALUE]
                    )
                    ->where(ResultValueInterface::FIELD_ID . '=?', $fieldId);
                $this->getSelect()->joinLeft(
                    ['results_values_' . $fieldId => $resultValues],
                    'main_table. ' . ResultInterface::ID . ' = results_values_' . $fieldId . '._' . ResultValueInterface::RESULT_ID,
                    ["field_$fieldId" => "results_values_$fieldId." . ResultValueInterface::VALUE]
                );
            }
        }
    }

    /**
     * Get fields ids
     *
     * @param int $webformId
     * @return array
     */
    protected function getFieldsIds(int $webformId): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(FieldResource::DB_TABLE), [
                FieldInterface::ID
            ])
            ->where(FieldInterface::FORM_ID . '=?', $webformId);
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @inheritDoc
     */
    protected function setDataFromResultValues(): ResultCollection
    {
        $webformId = (int)$this->request->getParam(ResultInterface::FORM_ID);
        if ($webformId) {
            if (count($this->getFieldsIds($webformId)) > ResultInterface::MAX_JOIN_FIELDS) {
                return parent::setDataFromResultValues();
            }
        }
        return $this;
    }
}
