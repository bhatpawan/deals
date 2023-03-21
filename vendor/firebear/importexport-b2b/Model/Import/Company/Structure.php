<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\Company\Api\Data\StructureInterface;

class Structure extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = StructureInterface::STRUCTURE_ID;
    const COLUMN_CUSTOMER_ID = StructureInterface::ENTITY_ID;
    const COLUMN_PATH = StructureInterface::PATH;

    public function getAllFields()
    {
        return [];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_CUSTOMER_ID.' = ?', $this->getOldCustomerIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $customerId = $this->getCustomerIdFromMap();
        if ($customerId) {
            $newEntity = false;
            $entityId = $this->getExistEntityId($rowData);
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_CUSTOMER_ID => $customerId,
                static::COLUMN_PATH => $entityId
            ];

            /* prepare data */
            $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
            if ($newEntity) {
                $toCreate[] = $entityRow;
            } else {
                $toUpdate[] = $entityRow;
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * @return bool
     */
    public function rowsCanBeSkipped(): bool
    {
        return false;
    }
}
