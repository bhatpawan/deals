<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\RequisitionList;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\ResourceModel\RequisitionListItem;

/**
 * @method DateTime getDataDateTime
 * @method RequisitionListItem getDataRequisitionListItemResourceModel
 */
class Entity extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = RequisitionListInterface::REQUISITION_LIST_ID;
    const COLUMN_CUSTOMER_ID = RequisitionListInterface::CUSTOMER_ID;
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';
    const COLUMN_NAME = RequisitionListInterface::NAME;
    const COLUMN_UPDATED_AT = RequisitionListInterface::UPDATED_AT;

    /**
     * Error Codes
     */
    const ERROR_CUSTOMER_NOT_FOUND = 'requisitionListCustomerNotFound';
    const ERROR_NAME_IS_EMPTY = 'requisitionListNameIsEmpty';
    const ERROR_REQUISITION_LIST_NOT_FOUND = 'requisitionListNotFound';

    protected $_messageTemplates = [
        self::ERROR_CUSTOMER_NOT_FOUND => 'Requisition list customer not found',
        self::ERROR_NAME_IS_EMPTY => 'Requisition list %s is empty',
        self::ERROR_REQUISITION_LIST_NOT_FOUND => 'Requisition list not found'
    ];

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID
    ];

    protected $optGroupName = 'Requisition List';

    protected $enableReplace = true;

    public function prepareRowData(array $rowData)
    {
        $rowData = parent::prepareRowData($rowData);

        $columnCustomerId = static::COLUMN_CUSTOMER_ID;
        $columnCustomerEmail = static::COLUMN_CUSTOMER_EMAIL;

        if (empty($rowData[$columnCustomerId]) && !empty($rowData[$columnCustomerEmail])) {
            $rowData[$columnCustomerId] = $this->getCustomerIdByEmail($rowData[$columnCustomerEmail]);
        }

        return $rowData;
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnCustomerId = static::COLUMN_CUSTOMER_ID;
        $columnName = static::COLUMN_NAME;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnCustomerId.' = ?', $rowData[$columnCustomerId])
            ->where($columnName.' = ?', $rowData[$columnName]);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve customer identifier by email
     *
     * @param string $email
     * @return bool|int
     */
    protected function getCustomerIdByEmail($email)
    {
        $select = $this->_connection->select()
            ->from($this->getCustomerTable(), 'entity_id')
            ->where(CustomerInterface::EMAIL.' = ?', $email);

        return $this->_connection->fetchOne($select);
    }

    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        $columnCustomerId = static::COLUMN_CUSTOMER_ID;
        if (empty($rowData[$columnCustomerId]) ||
            !$this->validateForeignKey($columnCustomerId, $rowData[$columnCustomerId])) {
            $this->addRowError(static::ERROR_CUSTOMER_NOT_FOUND, $rowNumber, $columnCustomerId);
        }

        $columnName = static::COLUMN_NAME;
        if (empty($rowData[$columnName])) {
            $this->addRowError(static::ERROR_NAME_IS_EMPTY, $rowNumber, $columnName);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        return $this->checkUniqueKey($rowData, $rowNumber);
    }

    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            if (!$this->getExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_REQUISITION_LIST_NOT_FOUND, $rowNumber);
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $newEntity = false;
        $entityId = $this->getExistEntityId($rowData);
        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
        }

        $requisitionListIdsMap = $this->storage->getRequisitionListIdsMap();
        $requisitionListIdsMap[$this->incrementRequisitionListId] = $entityId;
        $this->storage->setRequisitionListIdsMap($requisitionListIdsMap);

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId
        ];

        if (empty($entityRow[static::COLUMN_UPDATED_AT])) {
            $entityRow[static::COLUMN_UPDATED_AT] = $this->getDataDateTime()->gmtDate();
        }

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

    protected function _getIdForDelete(array $rowData)
    {
        return $this->getExistEntityId($rowData);
    }

    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        if ($this->getBehavior() == Import::BEHAVIOR_REPLACE) {
            $this->_connection->delete(
                $this->getTableFromModel($this->getDataRequisitionListItemResourceModel()),
                [
                    RequisitionListItemInterface::REQUISITION_LIST_ID.' IN (?)' =>
                        array_column($toUpdate, static::COLUMN_ENTITY_ID)
                ]
            );
        }

        return parent::_saveEntities($toCreate, $toUpdate);
    }
}
