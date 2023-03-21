<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\SharedCatalog;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Tax\Model\ClassModel;

class CustomerGroup extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = 'customer_group_id';
    const COLUMN_CUSTOMER_GROUP_CODE = 'customer_group_code';
    const COLUMN_TAX_CLASS_ID = GroupInterface::TAX_CLASS_ID;

    /**
     * Error Codes
     */
    const ERROR_NAME_IS_EMPTY = 'customerGroupNameIsEmpty';
    const ERROR_TAX_CLASS_ID_IS_EMPTY = 'customerGroupTaxClassIdIsEmpty';
    const ERROR_TAX_CLASS_ID_IS_INVALID = 'customerGroupTaxClassIdIsInvalid';

    protected $_messageTemplates = [
        self::ERROR_NAME_IS_EMPTY => 'Customer group %s is empty',
        self::ERROR_TAX_CLASS_ID_IS_EMPTY => 'Customer group %s is empty',
        self::ERROR_TAX_CLASS_ID_IS_INVALID => 'Customer group %s is invalid'
    ];

    /**
     * Tax Class Table
     *
     * @var string
     */
    protected $_taxClassTable = 'tax_class';

    public function getAllFields()
    {
        return [
            GroupInterface::TAX_CLASS_ID
        ];
    }

    /**
     * Retrieve Tax Class Table Name
     *
     * @return string
     */
    protected function getTaxClassTable()
    {
        return $this->_resource->getTableName(
            $this->_taxClassTable
        );
    }

    protected function getExistEntityId(array $rowData)
    {
        $column = static::COLUMN_CUSTOMER_GROUP_CODE;
        $name = $rowData[static::COLUMN_NAME];
        $customerGroupIdsMap = $this->storage->getCustomerGroupIdsMap();

        if (!isset($customerGroupIdsMap[$name])) {
            $bind = [':'.$column => $name];

            $select = $this->_connection->select()
                ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
                ->where($column.' = :'.$column);

            $customerGroupIdsMap[$name] = $this->_connection->fetchOne($select, $bind);
            $this->storage->setCustomerGroupIdsMap($customerGroupIdsMap);
        }

        return $customerGroupIdsMap[$name];
    }

    /**
     * Validate Tax Class Identifier
     *
     * @param $taxClassId
     * @return bool|int
     */
    protected function validateTaxClassId($taxClassId)
    {
        $column = ClassModel::KEY_ID;
        $bind = [':'.$column => $taxClassId];

        $select = $this->_connection->select()
            ->from($this->getTaxClassTable(), $column)
            ->where($column.' = :'.$column);

        return $this->_connection->fetchOne($select, $bind);
    }

    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        $columnName = static::COLUMN_NAME;
        if (empty($rowData[$columnName])) {
            $this->addRowError(static::ERROR_NAME_IS_EMPTY, $rowNumber, $columnName);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);
            $columnTaxClassId = static::COLUMN_TAX_CLASS_ID;

            if ((isset($rowData[$columnTaxClassId]) || !$entityId) && empty($rowData[$columnTaxClassId])) {
                $this->addRowError(static::ERROR_TAX_CLASS_ID_IS_EMPTY, $rowNumber, $columnTaxClassId);
            }

            if (!empty($rowData[$columnTaxClassId]) && !$this->validateTaxClassId($rowData[$columnTaxClassId])) {
                $this->addRowError(static::ERROR_TAX_CLASS_ID_IS_INVALID, $rowNumber, $columnTaxClassId);
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $newEntity = false;
        $entityId = $this->getExistEntityId($rowData);
        $name = $rowData[static::COLUMN_NAME];

        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
        }

        $customerGroupIdsMap = $this->storage->getCustomerGroupIdsMap();
        $customerGroupIdsMap[$name] = $entityId;
        $this->storage->setCustomerGroupIdsMap($customerGroupIdsMap);

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId,
            static::COLUMN_CUSTOMER_GROUP_CODE => $name
        ];

        /* prepare data */
        $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
        if ($newEntity) {
            $toCreate[] = $entityRow;
        } else {
            $toUpdate[] = $entityRow;
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
