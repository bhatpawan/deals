<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\Company\Api\Data\CompanyCustomerInterface;

class CustomerAdvanced extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CompanyCustomerInterface::CUSTOMER_ID;
    const COLUMN_COMPANY_ID = CompanyCustomerInterface::COMPANY_ID;
    const COLUMN_STATUS = CompanyCustomerInterface::STATUS;

    public function getAllFields()
    {
        return [
            CompanyCustomerInterface::JOB_TITLE
        ];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $this->getCustomerIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function getStatusForExistEntity(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_STATUS)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $this->getCustomerIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $companyId = $this->getCompanyIdFromMap();
        $customerId = $this->getCustomerIdFromMap();
        if ($companyId && $customerId) {
            $newEntity = true;
            $status = CompanyCustomerInterface::STATUS_ACTIVE;

            if ($this->getExistEntityId($rowData)) {
                $newEntity = false;
                $status = $this->getStatusForExistEntity($rowData);
            }

            if (isset($rowData[Customer::COLUMN_IS_ACTIVE]) &&
                in_array($rowData[Customer::COLUMN_IS_ACTIVE], ['0', '1'])
            ) {
                $status = (int)$rowData[Customer::COLUMN_IS_ACTIVE];
            }

            $entityRow = [
                static::COLUMN_ENTITY_ID => $customerId,
                static::COLUMN_COMPANY_ID => $companyId,
                static::COLUMN_STATUS => $status
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

    protected function _getIdForDelete(array $rowData)
    {
        return $this->getCompanyIdFromMap();
    }

    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        $this->_connection->update(
            $this->getMainTable(),
            [static::COLUMN_COMPANY_ID => 0],
            [static::COLUMN_ENTITY_ID.' IN (?)' => $this->storage->getOldCustomerIdsMap()]
        );

        return parent::_saveEntities($toCreate, $toUpdate);
    }

    protected function _deleteEntities(array $toDelete)
    {
        $this->_connection->update(
            $this->getMainTable(),
            [static::COLUMN_COMPANY_ID => 0],
            [static::COLUMN_COMPANY_ID.' IN (?)' => $toDelete]
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function rowsCanBeSkipped(): bool
    {
        return false;
    }
}
