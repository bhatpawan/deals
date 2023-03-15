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

namespace MageMe\WebForms\Model\ResourceModel\Result;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\Result as ResultResource;
use MageMe\WebForms\Model\ResourceModel\ResultValue as ResultValueResource;
use MageMe\WebForms\Model\Result;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;
use Zend_Db_Select;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\Result
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ResultInterface::ID;

    /**
     * @var bool
     */
    protected $loadValues = false;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Collection constructor.
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
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fieldRepository       = $fieldRepository;
        $this->customerRepository    = $customerRepository;

    }

    /**
     * @param bool $load
     * @return $this
     */
    public function setLoadValues(bool $load): Collection
    {
        $this->loadValues = $load;
        return $this;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getSelectCountSql(): Select
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'customer') {

            // custom condition
            $value = substr((string)$condition['like'], 1, -1);
            while (strstr($value, "  ")) {
                $value = str_replace("  ", " ", $value);
            }
            $customersArray = [];

            $name      = explode(" ", $value);
            $firstname = $name[0];

            $lastname = $name[count($name) - 1];

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(CustomerInterface::FIRSTNAME, $firstname);
            if (count($name) == 2) {
                $searchCriteria->addFilter(CustomerInterface::LASTNAME, $lastname);
            }
            $searchCriteria = $searchCriteria->create();

            $customers = $this->customerRepository->getList($searchCriteria)->getItems();
            foreach ($customers as $customer) {
                $customersArray[] = $customer->getId();
            }
            return parent::addFieldToFilter(ResultInterface::CUSTOMER_ID, ['in' => $customersArray]);
        }
        if (isset($condition['gteq']) && !$this->validateDate($condition['gteq'])) {
            $condition['gteq'] = floatval($condition['gteq']);
        }
        if (isset($condition['lteq']) && !$this->validateDate($condition['lteq'])) {
            $condition['lteq'] = floatval($condition['lteq']);
        }
        if (is_string($field) && preg_match('/results_values_(\d+).value/', $field, $matches)) {
            $resultCondition = $this->getResultValueCondition((int)$matches[1], $condition);
            $this->_select->where($resultCondition, null, Select::TYPE_CONDITION);
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    private function validateDate($date): bool
    {
        return (bool)strtotime($date);
    }

    /**
     * @param int $fieldId
     * @param mixed $value
     * @param bool $strict
     * @return $this
     * @throws LocalizedException
     */
    public function addFieldFilter(int $fieldId, $value, bool $strict = false): Collection
    {
        $prefix = $strict ? '' : '%';
        $field  = $this->fieldRepository->getById($fieldId);
        $value  = $field->getValueForResultCollectionFilter($value);
        $cond   = $field->getResultCollectionFilterCondition($value, $prefix);
        $this->getSelect()
            ->join(
                ['results_values_' . $fieldId => $this->getTable(ResultValueResource::DB_TABLE)],
                'main_table.' . ResultInterface::ID . ' = results_values_' . $fieldId . '.' . ResultValueInterface::RESULT_ID,
                ['main_table.*']
            )
            ->group('main_table.' . ResultInterface::ID);

        $this->getSelect()
            ->where("results_values_$fieldId." . ResultValueInterface::FIELD_ID . " = $fieldId AND $cond");

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Result::class, ResultResource::class);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        return $this->prepareCollection();
    }

    public function prepareCollection(): Collection
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad()
    {
        $this->_eventManager->dispatch('webforms_result_collection_load', ['collection' => $this]);

        parent::_afterLoad();

        if ($this->loadValues) {
            $this->setDataFromResultValues();
        }
        return $this;
    }

    /**
     * Set data from result_values table
     *
     * @return $this
     */
    protected function setDataFromResultValues(): Collection
    {

        /** @var ResultInterface|Result $result */
        foreach ($this as $result) {
            if (!$result->getId()) {
                continue;
            }
            $query  = $this->getConnection()->select()
                ->from($this->getTable(ResultValueResource::DB_TABLE))
                ->where(ResultValueInterface::RESULT_ID . '=?', $result->getId());
            $items = $this->getConnection()->fetchAll($query);
            foreach ($items as $item) {
                $fieldId = $item[ResultValueInterface::FIELD_ID];
                $value = $item[ResultValueInterface::VALUE];
                $result->setData('field_' . $fieldId, $value);
            }
        }
        return $this;
    }

    /**
     * @param int $fieldId
     * @param null|string|array $condition
     * @return string
     */
    protected function getResultValueCondition(int $fieldId, $condition): string
    {
        $resultCondition = $this->_translateCondition($this->getTable(ResultValueResource::DB_TABLE) . '.' . ResultValueInterface::VALUE, $condition);
        $query  = $this->getConnection()->select()
            ->from($this->getTable(ResultValueResource::DB_TABLE),
                ['COUNT(' . $this->getTable(ResultValueResource::DB_TABLE) . '.' . ResultValueInterface::ID . ')'])
            ->where($this->getTable(ResultValueResource::DB_TABLE) . '.' . ResultValueInterface::RESULT_ID . ' = main_table.' . ResultInterface::ID)
            ->where($this->getTable(ResultValueResource::DB_TABLE) . '.' . ResultValueInterface::FIELD_ID . ' = ?', $fieldId)
            ->where($resultCondition, null, Select::TYPE_CONDITION);
        return '(' . $query->__toString() . ') > 0';
    }
}
