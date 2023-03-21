<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\CompanyCredit\Api\Data\CreditLimitInterface;

class Credit extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CreditLimitInterface::CREDIT_ID;
    const COLUMN_COMPANY_ID = CreditLimitInterface::COMPANY_ID;
    const COLUMN_EXCEED_LIMIT = CreditLimitInterface::EXCEED_LIMIT;
    const COLUMN_BALANCE = CreditLimitInterface::BALANCE;

    public function getAllFields()
    {
        return [
            CreditLimitInterface::BALANCE,
            CreditLimitInterface::CURRENCY_CODE,
            CreditLimitInterface::CREDIT_LIMIT,
            CreditLimitInterface::EXCEED_LIMIT
        ];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_COMPANY_ID.' = ?', $this->getCompanyIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $companyId = $this->getCompanyIdFromMap();
        if ($companyId) {
            $newEntity = false;
            $entityId = $this->getExistEntityId($rowData);
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $creditIdsMap = $this->storage->getCreditIdsMap();
            $creditIdsMap[$this->_processedRowsCount] = $entityId;
            $this->storage->setCreditIdsMap($creditIdsMap);

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_COMPANY_ID => $companyId
            ];

            $columnExceedLimit = static::COLUMN_EXCEED_LIMIT;
            if ((isset($rowData[$columnExceedLimit]) || $newEntity) && empty($rowData[$columnExceedLimit])) {
                $entityRow[$columnExceedLimit] = 0;
            } elseif (!empty($rowData[$columnExceedLimit])) {
                $entityRow[$columnExceedLimit] = $this->convertToBool($rowData[$columnExceedLimit]);
            }

            $columnBalance = static::COLUMN_BALANCE;
            if ((isset($rowData[$columnBalance]) || $newEntity) && empty($rowData[$columnBalance])) {
                $entityRow[$columnBalance] = 0;
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

    /**
     * @return bool
     */
    public function rowsCanBeSkipped(): bool
    {
        return false;
    }
}
