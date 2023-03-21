<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\SharedCatalog;

use Magento\Authorization\Model\UserContextInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Store\Model\Store;

/**
 * @method UserContextInterface getDataUserContext
 */
class Entity extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = SharedCatalogInterface::SHARED_CATALOG_ID;
    const COLUMN_CUSTOMER_GROUP_ID = SharedCatalogInterface::CUSTOMER_GROUP_ID;
    const COLUMN_TYPE = SharedCatalogInterface::TYPE;
    const COLUMN_CREATED_BY = SharedCatalogInterface::CREATED_BY;
    const COLUMN_STORE_ID = SharedCatalogInterface::STORE_ID;

    /**
     * Error Codes
     */
    const ERROR_NAME_IS_EMPTY = 'sharedCatalogNameIsEmpty';
    const ERROR_NOT_FOUND = 'sharedCatalogNotFound';

    protected $_messageTemplates = [
        self::ERROR_NAME_IS_EMPTY => 'Shared catalog %s is empty',
        self::ERROR_NOT_FOUND => 'Shared catalog not found'
    ];

    protected $enableReplace = true;

    public function getAllFields()
    {
        return [
            SharedCatalogInterface::NAME,
            SharedCatalogInterface::DESCRIPTION,
            SharedCatalogInterface::TYPE
        ];
    }

    protected function getExistEntityId(array $rowData)
    {
        $column = static::COLUMN_NAME;
        $bind = [':'.$column => $rowData[$column]];

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
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
        return $this->checkUniqueKey($rowData, $rowNumber);
    }

    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            if (!$this->getExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_NOT_FOUND, $rowNumber);
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

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId,
            static::COLUMN_CUSTOMER_GROUP_ID => $this->getCustomerGroupIdFromMap($rowData),
            static::COLUMN_CREATED_BY => $this->getDataUserContext()->getUserId(),
            static::COLUMN_STORE_ID => Store::DEFAULT_STORE_ID
        ];

        $columnType = static::COLUMN_TYPE;
        if ($newEntity || isset($rowData[$columnType])) {
            $entityRow[$columnType] = (isset($rowData[$columnType]) && strlen($rowData[$columnType])) ?
                $this->convertToBool($rowData[$columnType]) : SharedCatalogInterface::TYPE_CUSTOM;
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
}
