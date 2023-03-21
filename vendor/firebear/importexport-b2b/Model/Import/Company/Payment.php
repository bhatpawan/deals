<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

class Payment extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = 'company_id';
    const COLUMN_USE_CONFIG_SETTINGS = 'use_config_settings';

    public function getAllFields()
    {
        return [
            'applicable_payment_method',
            'available_payment_methods',
            static::COLUMN_USE_CONFIG_SETTINGS
        ];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $this->getCompanyIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $companyId = $this->getCompanyIdFromMap();
        if ($companyId) {
            $newEntity = !$this->getExistEntityId($rowData);

            $entityRow = [
                static::COLUMN_ENTITY_ID => $companyId
            ];

            $columnUseConfigSettings = static::COLUMN_USE_CONFIG_SETTINGS;
            if ((isset($rowData[$columnUseConfigSettings]) || $newEntity) &&
                empty($rowData[$columnUseConfigSettings])
            ) {
                $entityRow[$columnUseConfigSettings] = 1;
            } elseif (!empty($rowData[$columnUseConfigSettings])) {
                $entityRow[$columnUseConfigSettings] = $this->convertToBool($rowData[$columnUseConfigSettings]);
            }

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

    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        if ($toCreate) {
            $this->_connection->insertMultiple(
                $this->getMainTable(),
                $toCreate
            );
        }
        if ($toUpdate) {
            foreach ($toUpdate as $item) {
                $this->_connection->update(
                    $this->getMainTable(),
                    $item,
                    [static::COLUMN_ENTITY_ID.' = ?' => $item[static::COLUMN_ENTITY_ID]]
                );
            }
        }
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
