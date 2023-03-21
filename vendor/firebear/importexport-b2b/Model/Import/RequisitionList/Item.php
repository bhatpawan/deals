<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\RequisitionList;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * @method SerializerInterface getDataSerializer
 */
class Item extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = RequisitionListItemInterface::REQUISITION_LIST_ITEM_ID;
    const COLUMN_REQUISITION_LIST_ID = RequisitionListItemInterface::REQUISITION_LIST_ID;
    const COLUMN_SKU = RequisitionListItemInterface::SKU;
    const COLUMN_STORE_ID = RequisitionListItemInterface::STORE_ID;
    const COLUMN_QTY = RequisitionListItemInterface::QTY;
    const COLUMN_OPTIONS = RequisitionListItemInterface::OPTIONS;

    /**
     * Error Codes
     */
    const ERROR_SKU_IS_EMPTY = 'requisitionListItemSkuIsEmpty';
    const ERROR_SKU_IS_INVALID = 'requisitionListItemSkuIsInvalid';
    const ERROR_STORE_ID_IS_EMPTY = 'requisitionListStoreIdIsEmpty';
    const ERROR_STORE_ID_IS_INVALID = 'requisitionListStoreIdIsInvalid';
    const ERROR_QTY_IS_EMPTY = 'requisitionListItemQtyIsEmpty';
    const ERROR_QTY_IS_INVALID = 'requisitionListItemQtyIsInvalid';

    protected $_messageTemplates = [
        self::ERROR_SKU_IS_EMPTY => 'Requisition list item %s is empty',
        self::ERROR_SKU_IS_INVALID => 'Requisition list item %s is invalid',
        self::ERROR_STORE_ID_IS_EMPTY => 'Requisition list item %s is empty',
        self::ERROR_STORE_ID_IS_INVALID => 'Requisition list item %s is invalid',
        self::ERROR_QTY_IS_EMPTY => 'Requisition list item %s is empty',
        self::ERROR_QTY_IS_INVALID => 'Requisition list item %s is invalid'
    ];

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_REQUISITION_LIST_ID
    ];

    protected $optGroupName = 'Requisition List Item';

    protected $columnPrefix = 'item';

    public function prepareRowData(array $rowData)
    {
        $rowData = parent::prepareRowData($rowData);
        if ($rowData) {
            $requisitionListIdsMap = $this->storage->getRequisitionListIdsMap();
            if (!isset($requisitionListIdsMap[$this->incrementRequisitionListId])) {
                return false;
            }

            $rowData[static::COLUMN_REQUISITION_LIST_ID] = $requisitionListIdsMap[$this->incrementRequisitionListId];
        }

        return $rowData;
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnRequisitionListId = static::COLUMN_REQUISITION_LIST_ID;
        $columnSku = static::COLUMN_SKU;
        $columnOptions = static::COLUMN_OPTIONS;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnRequisitionListId.' = ?', $rowData[$columnRequisitionListId])
            ->where($columnSku.' = ?', $rowData[$columnSku]);

        if (!empty($rowData[$columnOptions])) {
            $select->where($columnOptions.' = ?', $rowData[$columnOptions]);
        } else {
            $select->where($columnOptions.' IS NULL');
        }

        return $this->_connection->fetchOne($select);
    }

    /**
     * Validate Quantity
     *
     * @param $qty
     * @return bool
     */
    protected function validateQty($qty)
    {
        return is_numeric($qty) && ($qty > 0);
    }

    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        $columnSku = static::COLUMN_SKU;
        if (empty($rowData[$columnSku])) {
            $this->addRowError(static::ERROR_SKU_IS_EMPTY, $rowNumber, $columnSku);
        } elseif (!$this->getProductIdBySku($rowData[$columnSku])) {
            $this->addRowError(static::ERROR_SKU_IS_INVALID, $rowNumber, $columnSku);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            foreach ([
                static::COLUMN_STORE_ID => static::ERROR_STORE_ID_IS_EMPTY,
                static::COLUMN_QTY => static::ERROR_QTY_IS_EMPTY] as $column => $errorCode) {
                if ((isset($rowData[$column]) || !$entityId) && empty($rowData[$column])) {
                    $this->addRowError($errorCode, $rowNumber, $column);
                }
            }

            $columnStoreId = static::COLUMN_STORE_ID;
            if (!empty($rowData[$columnStoreId]) &&
                !$this->validateForeignKey($columnStoreId, $rowData[$columnStoreId])
            ) {
                $this->addRowError(static::ERROR_STORE_ID_IS_INVALID, $rowNumber, $columnStoreId);
            }

            $columnQty = static::COLUMN_QTY;
            if (!empty($rowData[$columnQty]) && !$this->validateQty($rowData[$columnQty])) {
                $this->addRowError(static::ERROR_QTY_IS_INVALID, $rowNumber, $columnQty);
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $columnOptions = static::COLUMN_OPTIONS;
        if (empty($rowData[$columnOptions])) {
            $rowData[$columnOptions] = $this->getDataSerializer()->serialize([
                'info_buyRequest' => []
            ]);
        }

        $newEntity = false;
        $entityId = $this->getExistEntityId($rowData);
        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
        }

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId,
            static::COLUMN_REQUISITION_LIST_ID => $rowData[static::COLUMN_REQUISITION_LIST_ID]
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
